<?php

namespace V8\Core;

use DateTime;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use V8\Core\Exception\ValidationException;

abstract class DataTransferObject
{
    public function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }

        $this->validate();
    }

    public static function fromRequest(Request $request): static
    {
        return new static($request->toArray());
    }

    protected function validate(): void
    {
        $errors = [];
        $rules = $this->rules();
        $customMessages = method_exists($this, 'messages')
            ? $this->messages()
            : [];

        foreach ($rules as $field => $ruleDefinitions) {
            $value = $this->{$field} ?? null;
            $fieldName = $this->getFieldName($field);

            // Handle both string and array rule formats
            $rulesToCheck = is_string($ruleDefinitions)
                ? explode('|', $ruleDefinitions)
                : $ruleDefinitions;

            foreach ($rulesToCheck as $rule => $params) {
                if (is_int($rule)) { // For string format
                    $rule = $params;
                    $params = null;
                }

                // Required
                if ($rule === 'required' && empty($value)) {
                    $errors[$field] = $this->getMessage($field, $rule, $customMessages, $fieldName);
                    continue;
                }

                // Minimum Length
                if ($rule === 'min' && strlen($value) < $params) {
                    $errors[$field] = $this->getMessage($field, $rule, $customMessages, $fieldName, ['min' => $params]);
                }

                // Maximum Length
                if ($rule === 'max' && strlen($value) > $params) {
                    $errors[$field] = $this->getMessage($field, $rule, $customMessages, $fieldName, ['max' => $params]);
                }

                // Exact Length
                if ($rule === 'size' && strlen($value) != $params) {
                    $errors[$field] = $this->getMessage($field, $rule, $customMessages, $fieldName, ['size' => $params]);
                }

                // Email Format
                if ($rule === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = $this->getMessage($field, $rule, $customMessages, $fieldName);
                }

                // Numeric
                if ($rule === 'numeric' && !is_numeric($value)) {
                    $errors[$field] = $this->getMessage($field, $rule, $customMessages, $fieldName);
                }

                // Integer
                if ($rule === 'integer' && filter_var($value, FILTER_VALIDATE_INT) === false) {
                    $errors[$field] = $this->getMessage($field, $rule, $customMessages, $fieldName);
                }

                // Boolean
                if ($rule === 'boolean' && !in_array($value, [true, false, 0, 1, '0', '1'], true)) {
                    $errors[$field] = $this->getMessage($field, $rule, $customMessages, $fieldName);
                }

                // Accepted (checkbox-like truthy values)
                if ($rule === 'accepted' && !in_array($value, ['yes', 'on', 1, true, '1'], true)) {
                    $errors[$field] = $this->getMessage($field, $rule, $customMessages, $fieldName);
                }

                // In List
                if ($rule === 'in' && !in_array($value, $params)) {
                    $errors[$field] = $this->getMessage($field, $rule, $customMessages, $fieldName, ['values' => implode(', ', $params)]);
                }

                // Not In List
                if ($rule === 'not_in' && in_array($value, $params)) {
                    $errors[$field] = $this->getMessage($field, $rule, $customMessages, $fieldName, ['values' => implode(', ', $params)]);
                }

                // URL
                if ($rule === 'url' && !filter_var($value, FILTER_VALIDATE_URL)) {
                    $errors[$field] = $this->getMessage($field, $rule, $customMessages, $fieldName);
                }

                // Date
                if ($rule === 'date' && strtotime($value) === false) {
                    $errors[$field] = $this->getMessage($field, $rule, $customMessages, $fieldName);
                }

                // Date Format
                if ($rule === 'date_format') {
                    $d = DateTime::createFromFormat($params, $value);
                    if (!($d && $d->format($params) === $value)) {
                        $errors[$field] = $this->getMessage($field, $rule, $customMessages, $fieldName, ['format' => $params]);
                    }
                }

                // Same
                if ($rule === 'same' && ($value !== ($data[$params] ?? null))) {
                    $errors[$field] = $this->getMessage($field, $rule, $customMessages, $fieldName, ['other' => $params]);
                }

                // Different
                if ($rule === 'different' && ($value === ($data[$params] ?? null))) {
                    $errors[$field] = $this->getMessage($field, $rule, $customMessages, $fieldName, ['other' => $params]);
                }

                // Confirmed (checks for e.g. password_confirmation)
                if ($rule === 'confirmed' && $value !== ($data[$field . '_confirmation'] ?? null)) {
                    $errors[$field] = $this->getMessage($field, $rule, $customMessages, $fieldName);
                }
            }
        }

        if ($errors) {
            throw new ValidationException([
                'message' => 'Validation failed',
                'errors' => $errors
            ]);
        }
    }

    protected function messages(): array
    {
        return [];
    }

    protected function getFieldName(string $field): string
    {
        return $this->fieldNames()[$field] ?? $field;
    }

    protected function getMessage(
        string $field,
        string $rule,
        array $customMessages,
        string $fieldName,
        array $replace = []
    ): string {
        // First try direct field.rule key (flat format)
        $flatKey = "{$field}.{$rule}";
        if (isset($customMessages[$flatKey])) {
            return $customMessages[$flatKey];
        }

        // Then try nested array access (nested format)
        if (isset($customMessages[$field][$rule])) {
            return $customMessages[$field][$rule];
        }

        // Finally try mixed format (field.rule in nested array)
        foreach ($customMessages as $key => $value) {
            if (is_array($value)) {
                $nestedFlatKey = "{$field}.{$rule}";
                if (isset($value[$nestedFlatKey])) {
                    return $value[$nestedFlatKey];
                }
            }
        }

        $defaultMessages = [
            'required' => "{$fieldName} is required",
            'min' => isset($replace['min']) ?
                "{$fieldName} must be at least {$replace['min']} characters" :
                "{$fieldName} doesn't meet minimum requirements",
            'max' => isset($replace['max']) ?
                "{$fieldName} must be no more than {$replace['max']} characters" :
                "{$fieldName} exceeds maximum allowed",
            'size' => isset($replace['size']) ?
                "{$fieldName} must be exactly {$replace['size']} characters" :
                "{$fieldName} has incorrect length",
            'email' => "{$fieldName} must be a valid email address",
            'numeric' => "{$fieldName} must be a number",
            'integer' => "{$fieldName} must be an integer",
            'boolean' => "{$fieldName} must be true or false",
            'accepted' => "{$fieldName} must be accepted",
            'in' => isset($replace['values']) ?
                "{$fieldName} must be one of: {$replace['values']}" :
                "{$fieldName} has invalid value",
            'not_in' => isset($replace['values']) ?
                "{$fieldName} must not be one of: {$replace['values']}" :
                "{$fieldName} has invalid value",
            'url' => "{$fieldName} must be a valid URL",
            'date' => "{$fieldName} must be a valid date",
            'date_format' => isset($replace['format']) ?
                "{$fieldName} must match the format {$replace['format']}" :
                "{$fieldName} has invalid date format",
            'same' => isset($replace['other']) ?
                "{$fieldName} must match {$replace['other']}" :
                "{$fieldName} must match confirmation field",
            'different' => isset($replace['other']) ?
                "{$fieldName} must be different from {$replace['other']}" :
                "{$fieldName} must be different",
            'confirmed' => "{$fieldName} confirmation does not match"
        ];

        return $defaultMessages[$rule] ?? "Validation failed for {$fieldName}";
    }

    protected function fieldNames(): array
    {
        return [];
    }

    abstract protected function rules(): array;
}

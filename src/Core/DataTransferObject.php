<?php

namespace V8\Core;

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

        foreach ($this->rules() as $property => $rules) {
            $value = $this->{$property} ?? null;

            if (str_contains($rules, 'required') && empty($value)) {
                $errors[$property] = "$property is required";
            }


            if (!empty($value) && preg_match('/min:(\d+)/', $rules, $matches) && strlen($value) < (int)$matches[1]) {
                $errors[$property] = "$property must be at least {$matches[1]} characters";
            }

            if (!empty($value) && preg_match('/max:(\d+)/', $rules, $matches) && strlen($value) > (int)$matches[1]) {
                $errors[$property] = "$property must be no more than {$matches[1]} characters";
            }

            if (!empty($value) && str_contains($rules, 'email') && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[$property] = "$property must be a valid email address";
            }

            if (!empty($value) && str_contains($rules, 'numeric') && !is_numeric($value)) {
                $errors[$property] = "$property must be numeric";
            }

            if (!empty($value) && str_contains($rules, 'min:') && strlen($value) < (int) str_replace('min:', '', $rules)) {
                $errors[$property] = "$property must be at least " . str_replace('min:', '', $rules) . " characters";
            }

            if (!empty($value) && str_contains($rules, 'boolean') && !in_array($value, [true, false, 0, 1, '0', '1'], true)) {
                $errors[$property] = "$property must be true or false";
            }

            if (!empty($value) && str_contains($rules, 'url') && !filter_var($value, FILTER_VALIDATE_URL)) {
                $errors[$property] = "$property must be a valid URL";
            }

            // not_in:a,b,c
            if (!empty($value) && preg_match('/in:([a-zA-Z0-9,_-]+)/', $rules, $matches)) {
                $allowed = explode(',', $matches[1]);
                if (!in_array($value, $allowed)) {
                    $errors[$property] = "$property must be one of: " . implode(', ', $allowed);
                }
            }

            // same:otherProperty
            if (!empty($value) && preg_match('/same:([a-zA-Z_]+)/', $rules, $matches)) {
                $other = $matches[1];
                if ($value !== ($this->{$other} ?? null)) {
                    $errors[$property] = "$property must match $other";
                }
            }

            // regex:/pattern/
            if (!empty($value) && preg_match('/regex:(\/.*\/)/', $rules, $matches)) {
                if (!preg_match($matches[1], $value)) {
                    $errors[$property] = "$property format is invalid";
                }
            }

            if (!empty($value) && str_contains($rules, 'date') && strtotime($value) === false) {
                $errors[$property] = "$property must be a valid date";
            }

            if (!empty($value) && preg_match('/before:([\d\-]+)/', $rules, $matches)) {
                if (strtotime($value) >= strtotime($matches[1])) {
                    $errors[$property] = "$property must be before {$matches[1]}";
                }
            }

            if (!empty($value) && preg_match('/after:([\d\-]+)/', $rules, $matches)) {
                if (strtotime($value) <= strtotime($matches[1])) {
                    $errors[$property] = "$property must be after {$matches[1]}";
                }
            }

            if (empty($value) && str_contains($rules, 'nullable')) {
                continue;
            }

            if (!empty($value) && preg_match('/digits:(\d+)/', $rules, $matches)) {
                if (!ctype_digit($value) || strlen($value) != (int)$matches[1]) {
                    $errors[$property] = "$property must be {$matches[1]} digits";
                }
            }
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }
    }

    abstract protected function rules(): array;
}

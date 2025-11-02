<?php

declare(strict_types=1);

namespace Hyperdrive\Dto;

use Hyperdrive\Http\Request;
use Hyperdrive\Reflection\HyperdriveReflection;

abstract class DataTransferObject
{
    public static function fromArray(array $data): static
    {
        $reflection = new HyperdriveReflection(static::class);
        return $reflection->newInstanceWithData($data);
    }

    public static function fromRequest(Request $request): static
    {
        return static::fromArray($request->getData());
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }

    public function rules(): array
    {
        return [];
    }

    public function validate(): bool
    {
        return empty($this->getErrors());
    }

    public function getErrors(): array
    {
        $errors = [];
        $rules = $this->rules();

        foreach ($rules as $property => $rule) {
            if (!$this->validateProperty($property, $rule)) {
                $errors[$property] = "Failed validation: {$rule}";
            }
        }

        return $errors;
    }

    private function validateProperty(string $property, string $rule): bool
    {
        $value = $this->{$property} ?? null;

        $rules = explode('|', $rule);

        foreach ($rules as $singleRule) {
            if (!$this->applyRule($property, $value, $singleRule)) {
                return false;
            }
        }

        return true;
    }

    private function applyRule(string $property, mixed $value, string $rule): bool
    {
        $rule = strtolower($rule);

        return match ($rule) {
            'required' => $this->isRequiredValid($value),
            'email' => filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
            'string' => is_string($value),
            'int', 'integer' => is_int($value),
            'float' => is_float($value),
            'array' => is_array($value),
            'bool', 'boolean' => is_bool($value),
            'numeric' => is_numeric($value),
            default => true
        };
    }

    private function isRequiredValid(mixed $value): bool
    {
        if (is_string($value)) {
            return $value !== '';
        }

        if (is_array($value)) {
            return !empty($value);
        }

        if (is_int($value) || is_float($value)) {
            return true;
        }

        if (is_bool($value)) {
            return true;
        }

        return $value !== null;
    }
}

<?php

declare(strict_types=1);

namespace Hyperdrive\Reflection;

class HyperdriveReflection extends \ReflectionClass
{
    public static function getTypeName(?\ReflectionType $type): ?string
    {
        if ($type === null) {
            return null;
        }

        if ($type instanceof \ReflectionNamedType) {
            return $type->getName();
        }

        if ($type instanceof \ReflectionUnionType) {
            $types = $type->getTypes();
            return $types[0] instanceof \ReflectionNamedType ? $types[0]->getName() : null;
        }

        return null;
    }

    public static function getDefaultForType(?\ReflectionType $type): mixed
    {
        $typeName = self::getTypeName($type);

        return match ($typeName) {
            'int' => 0,
            'float' => 0.0,
            'bool' => false,
            'string' => '',
            'array' => [],
            default => null
        };
    }

    public static function convertValueToType(mixed $value, ?\ReflectionType $type): mixed
    {
        if ($type === null) {
            return $value;
        }

        $typeName = self::getTypeName($type);

        if ($value === null && $type->allowsNull()) {
            return null;
        }

        return match ($typeName) {
            'int' => (int) $value,
            'float' => (float) $value,
            'bool' => self::convertToBool($value),
            'string' => (string) $value,
            'array' => self::convertToArray($value),
            default => $value
        };
    }

    private static function convertToBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (bool) $value;
        }

        if (is_string($value)) {
            $value = strtolower($value);
            return in_array($value, ['true', '1', 'yes', 'on'], true);
        }

        return (bool) $value;
    }

    private static function convertToArray(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) && $value !== '') {
            return [$value];
        }

        if ($value === null) {
            return [];
        }

        return (array) $value;
    }

    public function newInstanceWithData(array $data): object
    {
        $constructor = $this->getConstructor();

        if ($constructor === null) {
            return $this->newInstance();
        }

        $parameters = [];
        foreach ($constructor->getParameters() as $parameter) {
            $paramName = $parameter->getName();
            $paramType = $parameter->getType();

            if (array_key_exists($paramName, $data)) {
                $value = $data[$paramName];
                $parameters[] = self::convertValueToType($value, $paramType);
            } elseif ($parameter->isDefaultValueAvailable()) {
                $parameters[] = $parameter->getDefaultValue();
            } else {
                $parameters[] = self::getDefaultForType($paramType);
            }
        }

        return $this->newInstanceArgs($parameters);
    }

    public function getPropertyTypes(): array
    {
        $types = [];

        foreach ($this->getProperties() as $property) {
            $types[$property->getName()] = self::getTypeName($property->getType());
        }

        return $types;
    }

    public function implementsInterface(\ReflectionClass|string $interface): bool
    {
        $interfaceName = $interface instanceof \ReflectionClass ? $interface->getName() : $interface;
        return in_array($interfaceName, $this->getInterfaceNames(), true);
    }

    public function getMethodsWithAttribute(string $attributeName): array
    {
        $methods = [];

        foreach ($this->getMethods() as $method) {
            if (!empty($method->getAttributes($attributeName))) {
                $methods[] = $method;
            }
        }

        return $methods;
    }
}

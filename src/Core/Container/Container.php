<?php

namespace V8\Core\Container;

use Psr\Container\ContainerInterface;
use Exception;

class Container implements ContainerInterface
{
    private array $bindings = [];
    private array $instances = [];

    public function get(string $id): mixed
    {
        if (!$this->has($id)) {
            // Auto-resolve unbound classes
            if (class_exists($id)) {
                return $this->resolveClass($id);
            }
            throw new Exception("Binding not found: {$id}");
        }

        // Handle singleton bindings
        if (isset($this->instances[$id]) && $this->instances[$id] !== null) {
            return $this->instances[$id];
        }

        $concrete = $this->bindings[$id] ?? $id;

        if (is_callable($concrete)) {
            $object = $concrete($this);
        } else {
            $object = new $concrete();
        }

        // Store singleton instances
        if (array_key_exists($id, $this->instances)) {
            $this->instances[$id] = $object;
        }

        return $object;
    }

    public function has(string $id): bool
    {
        return isset($this->bindings[$id]) || isset($this->instances[$id]);
    }

    public function bind(string $abstract, mixed $concrete): void
    {
        $this->bindings[$abstract] = $concrete;
    }

    public function singleton(string $abstract, mixed $concrete = null): void
    {
        if ($concrete === null) {
            $concrete = $abstract;
        }

        $this->bind($abstract, $concrete);
        $this->instances[$abstract] = null;
    }

    protected function resolveClass(string $class): object
    {
        $reflector = new \ReflectionClass($class);

        // Check if class is instantiable
        if (!$reflector->isInstantiable()) {
            throw new Exception("Class {$class} is not instantiable");
        }

        // Get constructor parameters
        $constructor = $reflector->getConstructor();
        if (is_null($constructor)) {
            return new $class();
        }

        // Resolve constructor dependencies
        $parameters = $constructor->getParameters();
        $dependencies = $this->resolveDependencies($parameters);

        return $reflector->newInstanceArgs($dependencies);
    }

    protected function resolveDependencies(array $parameters): array
    {
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $type = $parameter->getType();
            if (!$type instanceof \ReflectionNamedType || $type->isBuiltin()) {
                throw new Exception("Cannot resolve parameter {$parameter->getName()}");
            }

            $dependencies[] = $this->get($type->getName());
        }

        return $dependencies;
    }
}

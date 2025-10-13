<?php

declare(strict_types=1);

namespace Hyperdrive\Container;

use Hyperdrive\Contracts\Container\ContainerInterface;
use ReflectionClass;
use ReflectionParameter;

class Container implements ContainerInterface
{
    private array $bindings = [];
    private array $instances = [];
    private array $resolving = [];

    public function get(string $id): mixed
    {
        // Check instances first
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        // Check bindings
        if (isset($this->bindings[$id])) {
            return $this->resolveBinding($id);
        }

        // Auto-resolve class if it exists
        if (class_exists($id)) {
            return $this->build($id);
        }

        throw new ContainerException("Service {$id} not found");
    }

    public function has(string $id): bool
    {
        return isset($this->bindings[$id]) || 
               isset($this->instances[$id]) || 
               class_exists($id);
    }

    public function bind(string $abstract, $concrete = null): void
    {
        $this->bindings[$abstract] = $concrete ?? $abstract;
    }

    public function singleton(string $abstract, $concrete = null): void
    {
        $this->bind($abstract, $concrete);
    }

    public function instance(string $abstract, object $instance): void
    {
        $this->instances[$abstract] = $instance;
    }

    public function bound(string $abstract): bool
    {
        return isset($this->bindings[$abstract]) || isset($this->instances[$abstract]);
    }

    private function resolveBinding(string $id): mixed
    {
        $concrete = $this->bindings[$id];

        if (is_callable($concrete)) {
            return $concrete($this);
        }

        if (is_string($concrete)) {
            return $this->build($concrete);
        }

        return $concrete;
    }

    private function build(string $class): object
    {
        // Circular dependency detection
        if (isset($this->resolving[$class])) {
            throw new ContainerException("Circular dependency detected: {$class}");
        }

        $this->resolving[$class] = true;

        try {
            $reflector = new ReflectionClass($class);

            if (!$reflector->isInstantiable()) {
                throw new ContainerException("Class {$class} is not instantiable");
            }

            $constructor = $reflector->getConstructor();

            if ($constructor === null) {
                return new $class();
            }

            $dependencies = $this->resolveDependencies($constructor->getParameters());
            return $reflector->newInstanceArgs($dependencies);

        } finally {
            unset($this->resolving[$class]);
        }
    }

    private function resolveDependencies(array $parameters): array
    {
        return array_map(function (ReflectionParameter $parameter) {
            $type = $parameter->getType();

            // Resolve class dependencies
            if ($type && !$type->isBuiltin()) {
                return $this->get($type->getName());
            }

            // Use default value if available
            if ($parameter->isDefaultValueAvailable()) {
                return $parameter->getDefaultValue();
            }

            throw new ContainerException(
                "Cannot resolve parameter \${$parameter->getName()} in {$parameter->getDeclaringClass()?->getName()}"
            );
        }, $parameters);
    }
}
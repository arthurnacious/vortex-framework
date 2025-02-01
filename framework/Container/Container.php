<?php
namespace Framework\Container;

use Framework\Module\BaseModule;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;

class Container implements ContainerInterface
{
    private array $instances = [];
    private array $definitions = [];
    private array $modules = [];

    /**
     * Register a class or definition
     */
    public function register(string $id, ?object $instance = null): self
    {
        if ($instance) {
            $this->instances[$id] = $instance;
        } else {
            $this->definitions[$id] = $id;
        }
        return $this;
    }

    /**
     * Resolve a class, creating and caching its instance
     */
    public function resolve(string $id)
    {
        // Return existing instance if it exists
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        // Use definition or fall back to the class itself
        $concrete = $this->definitions[$id] ?? $id;

        try {
            $reflector = new ReflectionClass($concrete);
        } catch (ReflectionException) {
            throw new \Exception("No entry found for {$id}");
        }

        // Cannot instantiate abstract or interface
        if (!$reflector->isInstantiable()) {
            throw new \Exception("Class {$concrete} is not instantiable");
        }

        $constructor = $reflector->getConstructor();
        
        // No constructor, simple instantiation
        if (!$constructor) {
            $instance = $reflector->newInstance();
            $this->instances[$id] = $instance;
            return $instance;
        }

        // Resolve constructor dependencies
        $dependencies = [];
        foreach ($constructor->getParameters() as $parameter) {
            $type = $parameter->getType();
            
            if (!$type || $type->isBuiltin()) {
                // Skip if no type or built-in type
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                } else {
                    throw new \Exception("Cannot resolve parameter {$parameter->getName()}");
                }
            } else {
                // Recursively resolve dependency
                $dependencyClass = $type->getName();
                $dependencies[] = $this->get($dependencyClass);
            }
        }

        // Create instance with resolved dependencies
        $instance = $reflector->newInstanceArgs($dependencies);
        $this->instances[$id] = $instance;
        
        return $instance;
    }

    /**
     * gets a class definition
     */
    public function getDefinitions(): array 
    {
        return $this->definitions;
    }

    /**
     * PSR-11 get method
     */
    public function get(string $id)
    {
        // Try to resolve if not found
        if (!$this->has($id)) {
            return $this->resolve($id);
        }
        
        return $this->resolve($id);
    }

    /**
     * PSR-11 has method
     */
    public function has(string $id): bool
    {
        return isset($this->instances[$id]) || 
               isset($this->definitions[$id]) || 
               class_exists($id);
    }


    public function registerModule(BaseModule $module): self {
        $this->modules[] = $module;
        return $this;
    }

    public function getModules(): array {
        return $this->modules;
    }
}
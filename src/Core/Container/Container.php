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
            throw new Exception("Binding not found: {$id}");
        }

        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        $concrete = $this->bindings[$id];

        return is_callable($concrete)
            ? $concrete($this)
            : new $concrete();
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
}

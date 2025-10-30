<?php

declare(strict_types=1);

namespace Hyperdrive\Module;

use Hyperdrive\Contracts\Module\ModuleInterface;
use Hyperdrive\Contracts\Container\ContainerInterface;
use Hyperdrive\Attributes\Module as ModuleAttribute;

class ModuleRegistry
{
    private array $modules = [];
    private array $metadata = [];

    public function register(string $moduleClass, ContainerInterface $container): void
    {
        if (!class_exists($moduleClass)) {
            throw new \InvalidArgumentException("Module class {$moduleClass} does not exist");
        }

        if (!is_subclass_of($moduleClass, ModuleInterface::class)) {
            throw new \InvalidArgumentException("Module must implement ModuleInterface");
        }

        if (isset($this->modules[$moduleClass])) {
            return; // Already registered
        }

        /** @var ModuleInterface $module */
        $module = new $moduleClass();
        $this->modules[$moduleClass] = $module;

        // Extract module metadata from attribute
        $this->extractModuleMetadata($moduleClass);

        // Register the module
        $module->register($container);
    }

    public function boot(ContainerInterface $container): void
    {
        foreach ($this->modules as $module) {
            $module->boot($container);
        }
    }

    public function getModules(): array
    {
        return $this->modules;
    }

    public function getModuleMetadata(string $moduleClass): ?array
    {
        return $this->metadata[$moduleClass] ?? null;
    }

    public function getAllControllers(): array
    {
        $controllers = [];
        foreach ($this->modules as $module) {
            $controllers = array_merge($controllers, $module->getControllers());
        }
        return $controllers;
    }

    public function getAllProviders(): array
    {
        $providers = [];
        foreach ($this->modules as $module) {
            $providers = array_merge($providers, $module->getProviders());
        }
        return $providers;
    }

    public function getAllMiddlewares(): array
    {
        $middlewares = [];
        foreach ($this->modules as $module) {
            $middlewares = array_merge($middlewares, $module->getMiddlewares());
        }
        return $middlewares;
    }

    private function extractModuleMetadata(string $moduleClass): void
    {
        $reflection = new \ReflectionClass($moduleClass);
        $attributes = $reflection->getAttributes(ModuleAttribute::class);

        if (empty($attributes)) {
            $this->metadata[$moduleClass] = [
                'name' => '',
                'version' => '',
                'imports' => [],
                'providers' => [],
                'listeners' => [],
                'routes' => []
            ];
            return;
        }

        /** @var ModuleAttribute $attribute */
        $attribute = $attributes[0]->newInstance();

        $this->metadata[$moduleClass] = [
            'name' => $attribute->name,
            'version' => $attribute->version,
            'imports' => $attribute->imports,
            'providers' => $attribute->providers,
            'listeners' => $attribute->listeners,
            'routes' => $attribute->routes
        ];
    }
}

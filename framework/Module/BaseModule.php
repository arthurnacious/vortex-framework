<?php
namespace Framework\Module;

use Framework\Container\Container;

abstract class BaseModule
{
    /**
     * Register module services and dependencies
     */
    public function register(Container $container): void
    {
        // Register services
        foreach ($this->getServices() as $service) {
            $container->resolve($service);
        }

        // Register controllers
        foreach ($this->getControllers() as $controller) {
            $container->resolve($controller);
        }
    }

    /**
     * Get services to be registered
     * 
     * @return array List of service classes
     */
    public function getServices(): array
    {
        return [];
    }

    /**
     * Get controllers to be registered
     * 
     * @return array List of controller classes
     */
    public function getControllers(): array
    {
        return [];
    }

    /**
     * Get module-specific middleware
     * 
     * @return array Middleware classes
     */
    public function getMiddleware(): array
    {
        return [];
    }
}
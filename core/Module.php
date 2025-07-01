<?php

namespace V8;

use V8\Container\Container;

abstract class Module
{
    public function __construct(
        protected Container $container
    ) {}

    abstract public function register(): void;

    protected function registerRoutes(array $controllers): void
    {
        $router = $this->container->get(Router::class);

        foreach ($controllers as $controller) {
            if (class_exists($controller)) {
                $router->registerController($controller);
            }
        }
    }

    protected function registerServices(array $services): void
    {
        foreach ($services as $abstract => $concrete) {
            $this->container->bind($abstract, $concrete);
        }
    }
}

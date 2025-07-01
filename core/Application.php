<?php

namespace V8;

use V8\Container\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Application
{
    private Container $container;

    public function __construct(
        private string $basePath
    ) {
        $this->bootstrap();
    }

    private function bootstrap(): void
    {
        $this->initializeContainer();
        $this->registerCoreBindings();
    }

    private function initializeContainer(): void
    {
        $this->container = new Container();
        $this->container->singleton(self::class, $this);
    }

    private function registerCoreBindings(): void
    {
        // Bind the Router with container reference
        $this->container->singleton(Router::class, function () {
            return new Router($this->container);
        });

        $this->container->singleton(Request::class, fn() => Request::createFromGlobals());
        $this->container->singleton(Response::class);
    }

    public function registerModules(array $modules): void
    {
        foreach ($modules as $moduleClass) {
            $module = new $moduleClass($this->container);
            $module->register();
        }
    }

    public function run(): void
    {
        $request = $this->container->get(Request::class);
        $router = $this->container->get(Router::class);
        $response = $router->dispatch($request);
        $response->send();
    }

    public function getContainer(): Container
    {
        return $this->container;
    }
}

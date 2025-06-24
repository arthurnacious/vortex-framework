<?php

namespace V8\Core;

use V8\Core\Container\Container;
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
        // Bind Request with factory closure
        $this->container->singleton(Request::class, fn() => Request::createFromGlobals());

        // Bind Response to itself (auto-instantiate)
        $this->container->singleton(Response::class, Response::class);
    }

    public function run(): void
    {
        $response = $this->container->get(Response::class);
        $response->setContent("V8 Framework Running!");
        $response->send();
    }

    public function getContainer(): Container
    {
        return $this->container;
    }
}

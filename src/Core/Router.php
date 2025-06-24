<?php

namespace V8\Core;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use V8\Core\Container\Container;

class Router
{
    private Dispatcher $dispatcher;
    private RouteCollector $routeCollector;

    public function __construct(
        private Container $container
    ) {
        $this->routeCollector = new RouteCollector(
            new \FastRoute\RouteParser\Std(),
            new \FastRoute\DataGenerator\GroupCountBased()
        );
    }

    public function registerController(string $controllerClass): void
    {
        $reflection = new \ReflectionClass($controllerClass);

        // Get class-level route prefix
        $classRoute = null;
        foreach ($reflection->getAttributes(\V8\Core\Attributes\Route::class) as $attribute) {
            $classRoute = $attribute->newInstance();
        }

        foreach ($reflection->getMethods() as $method) {
            foreach ($method->getAttributes() as $attribute) {
                $instance = $attribute->newInstance();

                if ($instance instanceof \V8\Core\Attributes\HttpMethod) {
                    $path = ($classRoute?->path ?? '') . $instance->path;
                    $this->routeCollector->addRoute(
                        $instance->method,
                        $path,
                        [$controllerClass, $method->getName()]
                    );
                }
            }
        }
    }

    public function dispatch(Request $request): Response
    {
        $this->dispatcher = new \FastRoute\Dispatcher\GroupCountBased($this->routeCollector->getData());

        $path = $request->getPathInfo();

        if (!str_ends_with($path, '/')) {
            $path .= '/';
        }

        $routeInfo = $this->dispatcher->dispatch(
            $request->getMethod(),
            $path
        );

        return match ($routeInfo[0]) {
            Dispatcher::NOT_FOUND => new Response('Not Found', 404),
            Dispatcher::METHOD_NOT_ALLOWED => new Response('Method Not Allowed', 405),
            Dispatcher::FOUND => $this->handleFoundRoute($routeInfo),
            default => new Response('Server Error', 500)
        };
    }

    private function handleFoundRoute(array $routeInfo): Response
    {
        try {
            [$controllerClass, $methodName] = $routeInfo[1];
            $params = $routeInfo[2];

            $controller = $this->container->get($controllerClass);
            return $controller->$methodName(...array_values($params));
        } catch (\Throwable $e) {
            return new Response('Server Error: ' . $e->getMessage(), 500);
        }
    }
}

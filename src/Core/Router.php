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

        // Remove trailing slash for dynamic routes
        $path = rtrim($path, '/');

        $routeInfo = $this->dispatcher->dispatch(
            $request->getMethod(),
            $path
        );

        return match ($routeInfo[0]) {
            Dispatcher::NOT_FOUND => $this->handleNotFound($path),
            Dispatcher::METHOD_NOT_ALLOWED => new Response('Method Not Allowed', 405),
            Dispatcher::FOUND => $this->handleFoundRoute($routeInfo, $request),
            default => new Response('Server Error', 500)
        };
    }

    private function handleNotFound(string $path): Response
    {
        // Try again with trailing slash if not found
        $retryPath = $path . '/';
        $retryInfo = $this->dispatcher->dispatch(
            $_SERVER['REQUEST_METHOD'] ?? 'GET',
            $retryPath
        );

        if ($retryInfo[0] === Dispatcher::FOUND) {
            return $this->handleFoundRoute($retryInfo, $request);
        }

        return new Response('Not Found', 404);
    }

    private function handleFoundRoute(array $routeInfo, Request $request): Response
    {
        try {
            [$controllerClass, $methodName] = $routeInfo[1];
            $params = $routeInfo[2];

            $controller = $this->container->get($controllerClass);
            $method = new \ReflectionMethod($controller, $methodName);

            $args = [];
            foreach ($method->getParameters() as $param) {
                $paramName = $param->getName();
                $paramType = $param->getType();

                if (array_key_exists($paramName, $params)) {
                    // Route parameter
                    $args[] = $this->castParameter($params[$paramName], $paramType);
                } elseif ($paramType && is_a($paramType->getName(), Request::class, true)) {
                    // Request object injection
                    $args[] = $request;
                } else {
                    // Service injection
                    $args[] = $this->container->get($paramType->getName());
                }
            }

            return $method->invokeArgs($controller, $args);
        } catch (\Throwable $e) {
            return new Response('Server Error: ' . $e->getMessage(), 500);
        }
    }

    private function castParameter($value, ?\ReflectionType $type)
    {
        if (!$type instanceof \ReflectionNamedType || $type->isBuiltin()) {
            return $value;
        }

        settype($value, $type->getName());
        return $value;
    }
}

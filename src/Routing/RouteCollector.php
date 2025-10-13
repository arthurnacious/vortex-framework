<?php

declare(strict_types=1);

namespace Hyperdrive\Routing;

use Hyperdrive\Attributes\Route as RouteAttribute;
use Hyperdrive\Attributes\Get;
use Hyperdrive\Attributes\Post;
use Hyperdrive\Attributes\Put;
use Hyperdrive\Attributes\Delete;
use ReflectionClass;
use ReflectionMethod;

class RouteCollector
{
    private array $routes = [];

    public function registerController(string $controllerClass): void
    {
        if (!class_exists($controllerClass)) {
            throw new \InvalidArgumentException("Controller class {$controllerClass} does not exist");
        }

        $reflection = new ReflectionClass($controllerClass);
        
        // Get class-level route attribute
        $classAttributes = $reflection->getAttributes(RouteAttribute::class);
        $prefix = '';
        $classMiddleware = [];

        foreach ($classAttributes as $attribute) {
            $routeAttribute = $attribute->newInstance();
            $prefix = $routeAttribute->prefix;
            $classMiddleware = $routeAttribute->middleware;
        }

        // Get method-level route attributes
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $this->registerMethodRoutes($method, $controllerClass, $prefix, $classMiddleware);
        }
    }

    public function registerControllers(array $controllerClasses): void
    {
        foreach ($controllerClasses as $controllerClass) {
            $this->registerController($controllerClass);
        }
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function getRoutesByMethod(string $method): array
    {
        return array_values(array_filter($this->routes, fn($route) => $route->method === strtoupper($method)));
    }

    private function registerMethodRoutes(ReflectionMethod $method, string $controllerClass, string $prefix, array $classMiddleware): void
    {
        $routeAttributes = [
            Get::class => 'GET',
            Post::class => 'POST', 
            Put::class => 'PUT',
            Delete::class => 'DELETE'
        ];

        foreach ($routeAttributes as $attributeClass => $httpMethod) {
            $attributes = $method->getAttributes($attributeClass);
            
            foreach ($attributes as $attribute) {
                $routeAttribute = $attribute->newInstance();
                
                $uri = $this->buildUri($prefix, $routeAttribute->path);
                $middleware = array_merge($classMiddleware, $routeAttribute->middleware);
                
                $this->routes[] = new Route(
                    $httpMethod,
                    $uri,
                    $controllerClass,
                    $method->getName(),
                    $routeAttribute->name,
                    $middleware
                );
            }
        }
    }

    private function buildUri(string $prefix, string $path): string
    {
        // Ensure both prefix and path are properly formatted
        $prefix = trim($prefix, '/');
        $path = trim($path, '/');
        
        if ($prefix && $path) {
            return '/' . $prefix . '/' . $path;
        }
        
        if ($prefix) {
            return '/' . $prefix;
        }
        
        if ($path) {
            return '/' . $path;
        }
        
        return '/';
    }
}
<?php

declare(strict_types=1);

namespace Hyperdrive\Routing;

class Router
{
    public function __construct(
        private RouteCollector $collector
    ) {
    }

    public function match(string $method, string $path): ?array
    {
        $routes = $this->collector->getRoutesByMethod($method);
        
        foreach ($routes as $route) {
            $pattern = $this->convertToPattern($route->uri);
            
            if (preg_match($pattern, $path, $matches)) {
                $parameters = $this->extractParameters($matches);
                
                return [
                    'route' => $route,
                    'parameters' => $parameters
                ];
            }
        }
        
        return null;
    }

    private function convertToPattern(string $uri): string
    {
        // Escape forward slashes and convert route parameters to regex
        $pattern = preg_quote($uri, '#');
        $pattern = preg_replace('/\\\{([^}]+)\\\}/', '(?<$1>[^/]+)', $pattern);
        return '#^' . $pattern . '$#';
    }

    private function extractParameters(array $matches): array
    {
        $parameters = [];
        
        foreach ($matches as $key => $value) {
            if (is_string($key)) {
                $parameters[$key] = $value;
            }
        }
        
        return $parameters;
    }

    public function getCollector(): RouteCollector
    {
        return $this->collector;
    }

    public function debugPattern(string $uri): string
    {
        return $this->convertToPattern($uri);
    }
}
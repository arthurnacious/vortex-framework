<?php

namespace Framework\Routing;

class Route
{
    private array $parameters = [];
    private array $middleware = [];
    private string $pattern;

    public function __construct(
        private string $method,
        private string $path,
        private string $controller,
        private string $action
    ) {
        $this->pattern = $this->buildPattern($path);
    }

    /**
     * Build regex pattern from route path
     */
    private function buildPattern(string $path): string
    {
        // Replace route parameters with regex patterns
        $pattern = preg_replace('/\{([^:}]+):([^}]+)\}/', '(?P<$1>$2)', $path);
        $pattern = preg_replace('/\{([^}]+)\}/', '(?P<$1>[^/]+)', $pattern);
        
        // Escape forward slashes and add delimiters
        return '#^' . str_replace('/', '\/', $pattern) . '$#';
    }

    /**
     * Check if the route matches the given URI and method
     */
    public function matches(string $uri, string $method): bool
    {
        if ($this->method !== $method) {
            return false;
        }

        if (preg_match($this->pattern, $uri, $matches)) {
            // Store named parameters
            foreach ($matches as $key => $value) {
                if (is_string($key)) {
                    $this->parameters[$key] = $value;
                }
            }
            return true;
        }

        return false;
    }

    /**
     * Add middleware to the route
     */
    public function middleware(string|array $middleware): self
    {
        $this->middleware = array_merge(
            $this->middleware,
            is_array($middleware) ? $middleware : [$middleware]
        );
        return $this;
    }

    /**
     * Get route parameters
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Get route middleware
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    /**
     * Get controller class
     */
    public function getController(): string
    {
        return $this->controller;
    }

    /**
     * Get controller action
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * Get HTTP method
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Get original path
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Generate URL for route with parameters
     */
    public function generateUrl(array $parameters = []): string
    {
        $path = $this->path;
        
        foreach ($parameters as $key => $value) {
            $path = str_replace("{{$key}}", $value, $path);
        }

        // Remove any remaining optional parameters
        $path = preg_replace('/\{[^}]+\}/', '', $path);
        
        return $path;
    }

    /**
     * Create a new route instance for GET method
     */
    public static function get(string $path, string $controller, string $action): self
    {
        return new self('GET', $path, $controller, $action);
    }

    /**
     * Create a new route instance for POST method
     */
    public static function post(string $path, string $controller, string $action): self
    {
        return new self('POST', $path, $controller, $action);
    }

    /**
     * Create a new route instance for PUT method
     */
    public static function put(string $path, string $controller, string $action): self
    {
        return new self('PUT', $path, $controller, $action);
    }

    /**
     * Create a new route instance for DELETE method
     */
    public static function delete(string $path, string $controller, string $action): self
    {
        return new self('DELETE', $path, $controller, $action);
    }
}
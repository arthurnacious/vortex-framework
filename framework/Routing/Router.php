<?php
namespace Framework\Routing;

use Framework\Container\Container;
use Framework\Attributes\Path;
use Framework\Attributes\Get;
use Framework\Attributes\Post;
use Framework\Attributes\Put;
use Framework\Attributes\Delete;
use Framework\Attributes\Patch;
use Framework\Module\BaseModule;
use ReflectionClass;
use ReflectionMethod;

class Router {
    private array $routes = [];
    private Container $container;

    public function __construct(Container $container) {
        $this->container = $container;
    }

    public function registerController(string $controllerClass): void {
        $reflection = new ReflectionClass($controllerClass);
        $controllerPath = '';

        // Get controller base path
        $pathAttributes = $reflection->getAttributes(Path::class);
        if (!empty($pathAttributes)) {
            $controllerPath = $pathAttributes[0]->getArguments()[0];
        }

        // Get all methods
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $httpMethods = [
                'GET' => Get::class, 
                'PATCH' => Patch::class, 
                'PUT' => Put::class, 
                'POST' => Post::class, 
                'DELETE' => Delete::class
            ];

            foreach ($httpMethods as $methodName => $attributeClass) {
                $attributes = $method->getAttributes($attributeClass);

                if (empty($attributes)) continue;

                $path = $attributes[0]->getArguments()[0] ?? '/';
                $fullPath = rtrim($controllerPath . $path, '/'); // Remove trailing slash
                
                // Convert `{param}` placeholders to regex
                $regexPath = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $fullPath);
                
                $this->routes[$methodName][] = [
                    'pattern' => "@^" . $regexPath . "$@",
                    'controller' => $controllerClass,
                    'method' => $method->getName(),
                    'params' => []
                ];
            }
        }
    }

    public function dispatch(): void {
        foreach ($this->container->getModules() as $definition) {
            $moduleClass = is_object($definition) ? get_class($definition) : $definition;

            if (is_subclass_of($moduleClass, BaseModule::class)) {
                $module = new $moduleClass();
                foreach ($module->getControllers() as $controller) {
                    $this->registerController($controller);
                }
            }
        }

        $method = $_POST['_method'] ?? $_SERVER['REQUEST_METHOD'];
        $uri = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

        // Match routes
        if (isset($this->routes[$method])) {
            foreach ($this->routes[$method] as $route) {
                if (preg_match($route['pattern'], $uri, $matches)) {
                    $controller = $this->container->resolve($route['controller']);
                    $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY); // Extract named params
                    
                    $response = call_user_func_array([$controller, $route['method']], $params);
                    
                    header('Content-Type: application/json');
                    echo json_encode($response);
                    return;
                }
            }
        }

        header("HTTP/1.0 404 Not Found");
        echo json_encode(['error' => 'Route not found']);
    }
}

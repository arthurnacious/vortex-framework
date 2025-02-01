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
                'Patch' => Patch::class, 
                'PUT' => Put::class, 
                'POST' => Post::class, 
                'DELETE' => Delete::class
            ];

            foreach ($httpMethods as $methodName => $attributeClass) {
                $attributes = $method->getAttributes($attributeClass);
                if (empty($attributes)) continue;

                $path = $attributes[0]->getArguments()[0] ?? '/';
                $fullPath = $controllerPath . $path;

                $this->routes[$methodName][$fullPath] = [
                    'controller' => $controllerClass,
                    'method' => $method->getName()
                ];
            }
        }
    }

    public function dispatch(): void {
        // Scan registered models for BaseModule classes
        foreach ($this->container->getModules() as $definition) {
            $moduleClass = is_object($definition) ? get_class($definition) : $definition;

            if (is_subclass_of($moduleClass, BaseModule::class)) {
                
                $module = new $moduleClass();
                foreach ($module->getControllers() as $controller) {
                    $this->registerController($controller);
                }
            }
        }

        $method = $_POST['_method'] ?? $_SERVER['REQUEST_METHOD']; //IF WE ARE USING JUST A NORMAL FORM, WITHOUT ANY HTTP VERBS, WE NEED TO USE _POST
        $uri = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') . '/'; //ALWAYS APPEND A SLASH TO THE URI
        
        if (isset($this->routes[$method][$uri])) {
            $route = $this->routes[$method][$uri];
            $controller = $this->container->resolve($route['controller']);
            $response = $controller->{$route['method']}();
            
            header('Content-Type: application/json');
            echo json_encode($response);
            return;
        }

        header("HTTP/1.0 404 Not Found");
        echo json_encode(['error' => 'Route not found']);
    }
}
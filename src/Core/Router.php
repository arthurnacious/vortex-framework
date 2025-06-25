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

                if (
                    $instance instanceof \V8\Core\Attributes\Path ||
                    $instance instanceof \V8\Core\Attributes\HttpMethod
                ) {

                    $path = ($classRoute?->path ?? '') . $instance->path;
                    $methodName = $instance instanceof \V8\Core\Attributes\Path
                        ? $instance->method
                        : (new \ReflectionClass($attribute->getName()))->getShortName();

                    $this->routeCollector->addRoute(
                        $methodName,
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
        $path = rtrim($path, '/');

        $routeInfo = $this->dispatcher->dispatch(
            $request->getMethod(),
            $path
        );

        return match ($routeInfo[0]) {
            Dispatcher::NOT_FOUND => $this->handleNotFound($path, $request),
            Dispatcher::METHOD_NOT_ALLOWED => new Response('Method Not Allowed', 405),
            Dispatcher::FOUND => $this->handleFoundRoute($routeInfo, $request),
            default => new Response('Server Error', 500)
        };
    }

    private function handleNotFound(string $path, Request $request): Response
    {
        // Try again with trailing slash if not found
        $retryPath = $path . '/';
        $retryInfo = $this->dispatcher->dispatch(
            $request->getMethod(),  // Use the request method instead of $_SERVER
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
                if (is_subclass_of($param->getType()->getName(), DataTransferObject::class)) {
                    $args[] = $param->getType()->getName()::fromRequest($request);
                    continue;
                }

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

            $result = $method->invokeArgs($controller, $args);

            // Auto-convert non-Response returns
            if (!$result instanceof Response) {
                return $this->normalizeResponse($result);
            }
            return $result;
        } catch (\Throwable $e) {
            return new Response('Server Error: ' . $e->getMessage(), 500);
        }
    }

    private function normalizeResponse(mixed $data): Response
    {
        return match (true) {
            is_array($data) || is_object($data) => new Response(
                json_encode($data),
                Response::HTTP_OK,
                ['Content-Type' => 'application/json']
            ),
            is_scalar($data) => new Response((string)$data),
            $data === null   => new Response('', Response::HTTP_NO_CONTENT),
            default => throw new \RuntimeException('Unsupported return type')
        };
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

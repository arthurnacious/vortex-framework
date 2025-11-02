<?php

declare(strict_types=1);

namespace Hyperdrive\Http;

use Hyperdrive\Contracts\Container\ContainerInterface;
use Hyperdrive\Routing\Route;
use Hyperdrive\Dto\DataTransferObject;
use Hyperdrive\Reflection\HyperdriveReflection;
use ReflectionClass;
use ReflectionMethod;

class ControllerResolver
{
    public function resolve(Route $route, Request $request, ContainerInterface $container, array $parameters = []): Response
    {
        $controllerClass = $route->controller;
        $method = $route->action;

        if (!class_exists($controllerClass)) {
            throw new \InvalidArgumentException("Controller class {$controllerClass} does not exist");
        }

        if (!method_exists($controllerClass, $method)) {
            throw new \InvalidArgumentException("Method {$method} does not exist in controller {$controllerClass}");
        }

        // Resolve controller instance with dependencies
        $controller = $this->resolveController($controllerClass, $container);

        // Resolve method parameters
        $methodParameters = $this->resolveMethodParameters($controllerClass, $method, $request, $parameters, $container);

        // Call the controller method
        return $controller->$method(...$methodParameters);
    }

    private function resolveController(string $controllerClass, ContainerInterface $container): object
    {
        // Use container to resolve controller with dependencies
        if ($container->has($controllerClass)) {
            return $container->get($controllerClass);
        }

        $reflection = new ReflectionClass($controllerClass);
        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            return new $controllerClass();
        }

        $dependencies = [];
        foreach ($constructor->getParameters() as $parameter) {
            $type = $parameter->getType();

            if ($type && !$type->isBuiltin()) {
                $typeName = HyperdriveReflection::getTypeName($type);
                if ($typeName) {
                    $dependencies[] = $container->get($typeName);
                } else {
                    throw new \InvalidArgumentException(
                        "Cannot resolve parameter \${$parameter->getName()} in {$controllerClass} constructor"
                    );
                }
            } elseif ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
            } else {
                throw new \InvalidArgumentException(
                    "Cannot resolve parameter \${$parameter->getName()} in {$controllerClass} constructor"
                );
            }
        }

        return $reflection->newInstanceArgs($dependencies);
    }

    private function resolveMethodParameters(
        string $controllerClass,
        string $method,
        Request $request,
        array $routeParameters,
        ContainerInterface $container
    ): array {
        $reflection = new ReflectionMethod($controllerClass, $method);
        $parameters = [];

        foreach ($reflection->getParameters() as $parameter) {
            $paramName = $parameter->getName();
            $paramType = $parameter->getType();
            $typeName = $paramType ? HyperdriveReflection::getTypeName($paramType) : null;

            // Inject Request object
            if ($typeName === Request::class) {
                $parameters[] = $request;
                continue;
            }

            // Inject DTO objects
            if ($typeName && is_subclass_of($typeName, DataTransferObject::class)) {
                $parameters[] = $typeName::fromRequest($request);
                continue;
            }

            // Inject route parameters
            if (isset($routeParameters[$paramName])) {
                $parameters[] = $this->convertParameterType($routeParameters[$paramName], $paramType);
                continue;
            }

            // Use default value if available
            if ($parameter->isDefaultValueAvailable()) {
                $parameters[] = $parameter->getDefaultValue();
                continue;
            }

            throw new \InvalidArgumentException(
                "Cannot resolve parameter \${$paramName} in {$controllerClass}::{$method}"
            );
        }

        return $parameters;
    }

    private function convertParameterType(mixed $value, ?\ReflectionType $type): mixed
    {
        if ($type === null) {
            return $value;
        }

        $typeName = HyperdriveReflection::getTypeName($type);

        return match ($typeName) {
            'int' => (int) $value,
            'float' => (float) $value,
            'bool' => (bool) $value,
            'string' => (string) $value,
            'array' => (array) $value,
            default => $value
        };
    }
}

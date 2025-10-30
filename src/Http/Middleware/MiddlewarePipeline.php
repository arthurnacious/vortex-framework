<?php

declare(strict_types=1);

namespace Hyperdrive\Http\Middleware;

use Hyperdrive\Contracts\Middleware\MiddlewareInterface;
use Hyperdrive\Http\Request;
use Hyperdrive\Http\Response;
use Hyperdrive\Contracts\Container\ContainerInterface;

class MiddlewarePipeline
{
    private array $middlewares = [];

    public function __construct(
        private ?ContainerInterface $container = null
    ) {}

    public function pipe(MiddlewareInterface|string $middleware): self
    {
        $this->middlewares[] = $middleware;
        return $this;
    }

    public function process(Request $request, \Closure $finalHandler): Response
    {
        if (empty($this->middlewares)) {
            return $finalHandler($request);
        }

        $pipeline = $this->createPipeline($finalHandler);
        return $pipeline($request);
    }

    private function createPipeline(\Closure $finalHandler): \Closure
    {
        $next = $finalHandler;

        // Process middleware in reverse order so they execute in the order they were added
        foreach (array_reverse($this->middlewares) as $middleware) {
            $next = function (Request $request) use ($middleware, $next) {
                $middlewareInstance = $this->resolveMiddleware($middleware);
                return $middlewareInstance->handle($request, $next);
            };
        }

        return $next;
    }

    private function resolveMiddleware(MiddlewareInterface|string $middleware): MiddlewareInterface
    {
        if ($middleware instanceof MiddlewareInterface) {
            return $middleware;
        }

        if ($this->container && $this->container->has($middleware)) {
            return $this->container->get($middleware);
        }

        if (class_exists($middleware)) {
            return new $middleware();
        }

        throw new \InvalidArgumentException("Middleware {$middleware} could not be resolved");
    }

    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }
}

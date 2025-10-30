<?php

declare(strict_types=1);

namespace Hyperdrive\Kernel;

use Hyperdrive\Contracts\Kernel\KernelInterface;
use Hyperdrive\Contracts\Container\ContainerInterface;
use Hyperdrive\Container\Container;
use Hyperdrive\Http\Response;
use Hyperdrive\Http\Request;
use Hyperdrive\Routing\RouteCollector;
use Hyperdrive\Routing\Router;
use Hyperdrive\Http\ControllerResolver;
use Hyperdrive\Http\Middleware\MiddlewarePipeline;
use Hyperdrive\Module\ModuleRegistry;

class ApplicationKernel implements KernelInterface
{
    private float $frameworkStartTime;
    private float $frameworkBootTime = 0.0;
    private float $requestStartTime = 0.0;
    private bool $bootstrapped = false;
    private string $engine;
    private ContainerInterface $container;
    private Router $router;
    private ControllerResolver $controllerResolver;
    private MiddlewarePipeline $middlewarePipeline;
    private ModuleRegistry $moduleRegistry;

    public function __construct(
        private string $environment = 'production'
    ) {
        $this->frameworkStartTime = microtime(true);
        $this->engine = $this->detectEngine();
        $this->container = new Container();
        $this->router = new Router(new RouteCollector());
        $this->controllerResolver = new ControllerResolver();
        $this->middlewarePipeline = new MiddlewarePipeline($this->container);
        $this->moduleRegistry = new ModuleRegistry();

        // Register core services
        $this->registerCoreServices();
    }

    public function boost(): void
    {
        if ($this->bootstrapped) {
            return;
        }

        // Boot modules
        $this->bootModules();

        // Collect routes from modules
        $this->collectRoutes();

        $this->frameworkBootTime = microtime(true);
        $this->bootstrapped = true;
    }

    public function handle(): Response
    {
        $this->requestStartTime = microtime(true);

        // Create request from globals
        $request = Request::createFromGlobals();

        // Process through middleware pipeline
        $finalHandler = function (Request $request) {
            return $this->handleRequest($request);
        };

        $response = $this->middlewarePipeline->process($request, $finalHandler);

        return $response;
    }

    private function handleRequest(Request $request): Response
    {
        // Route the request
        $match = $this->router->match($request->getMethod(), $request->getPath());

        if ($match === null) {
            return Response::json(['error' => 'Route not found'], 404);
        }

        // Add route parameters to request attributes
        foreach ($match['parameters'] as $key => $value) {
            $request->setAttribute($key, $value);
        }

        // Resolve and call controller
        try {
            $response = $this->controllerResolver->resolve(
                $match['route'],
                $request,
                $this->container,
                $match['parameters']
            );
        } catch (\InvalidArgumentException $e) {
            return Response::json(['error' => $e->getMessage()], 500);
        }

        return $response;
    }

    public function registerModule(string $moduleClass): void
    {
        $this->moduleRegistry->register($moduleClass, $this->container);
    }

    public function registerModules(array $moduleClasses): void
    {
        foreach ($moduleClasses as $moduleClass) {
            $this->registerModule($moduleClass);
        }
    }

    public function pipeMiddleware($middleware): void
    {
        $this->middlewarePipeline->pipe($middleware);
    }

    public function getBootTimeMs(): float
    {
        if ($this->frameworkBootTime === 0.0) {
            return 0.0;
        }
        return ($this->frameworkBootTime - $this->frameworkStartTime) * 1000;
    }

    public function getResponseTimeMs(): float
    {
        if ($this->requestStartTime === 0.0) {
            return 0.0;
        }
        return (microtime(true) - $this->requestStartTime) * 1000;
    }

    public function getEnvironment(): string
    {
        return $this->environment;
    }

    public function isBootstrapped(): bool
    {
        return $this->bootstrapped;
    }

    public function getEngine(): string
    {
        return $this->engine;
    }

    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    public function getRouter(): Router
    {
        return $this->router;
    }

    public function getMiddlewarePipeline(): MiddlewarePipeline
    {
        return $this->middlewarePipeline;
    }

    public function getModuleRegistry(): ModuleRegistry
    {
        return $this->moduleRegistry;
    }

    private function detectEngine(): string
    {
        if (extension_loaded('openswoole')) {
            return 'openswoole';
        }

        if (extension_loaded('swoole')) {
            return 'swoole';
        }

        return 'roadster';
    }

    private function registerCoreServices(): void
    {
        // Register core framework services
        $this->container->instance(ContainerInterface::class, $this->container);
        $this->container->instance(KernelInterface::class, $this);
        $this->container->instance(Router::class, $this->router);
        $this->container->instance(ControllerResolver::class, $this->controllerResolver);
        $this->container->instance(MiddlewarePipeline::class, $this->middlewarePipeline);
        $this->container->instance(ModuleRegistry::class, $this->moduleRegistry);
        $this->container->bind('engine', fn() => $this->engine);
        $this->container->bind('environment', fn() => $this->environment);
    }

    private function bootModules(): void
    {
        $this->moduleRegistry->boot($this->container);
    }

    private function collectRoutes(): void
    {
        $controllers = $this->moduleRegistry->getAllControllers();
        $this->router->getCollector()->registerControllers($controllers);
    }
}

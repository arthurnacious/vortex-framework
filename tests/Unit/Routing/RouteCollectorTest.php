<?php

declare(strict_types=1);

namespace Hyperdrive\Tests\Unit\Routing;

use Hyperdrive\Routing\RouteCollector;
use Hyperdrive\Attributes\Route as RouteAttribute;
use Hyperdrive\Attributes\Get;
use Hyperdrive\Attributes\Post;
use PHPUnit\Framework\TestCase;

// Test controller classes
#[RouteAttribute('/api')]
class TestController
{
    #[Get('/users', 'users.index')]
    public function index(): array
    {
        return ['users' => []];
    }

    #[Post('/users', 'users.store')]
    public function store(): array
    {
        return ['user' => []];
    }
}

class PlainController
{
    #[Get('/plain', 'plain.index')]
    public function index(): array
    {
        return ['plain' => true];
    }
}

class RouteCollectorTest extends TestCase
{
    private RouteCollector $collector;

    protected function setUp(): void
    {
        $this->collector = new RouteCollector();
    }

    public function test_can_collect_routes_from_controller(): void
    {
        $this->collector->registerController(TestController::class);
        
        $routes = $this->collector->getRoutes();
        
        $this->assertCount(2, $routes);
        $this->assertEquals('GET', $routes[0]->method);
        $this->assertEquals('/api/users', $routes[0]->uri);
        $this->assertEquals('users.index', $routes[0]->name);
    }

    public function test_can_collect_routes_from_multiple_controllers(): void
    {
        $this->collector->registerControllers([
            TestController::class,
            PlainController::class
        ]);
        
        $routes = $this->collector->getRoutes();
        
        $this->assertCount(3, $routes);
    }

    public function test_route_prefix_is_applied(): void
    {
        $this->collector->registerController(TestController::class);
        
        $routes = $this->collector->getRoutes();
        
        $this->assertStringStartsWith('/api/', $routes[0]->uri);
        $this->assertStringStartsWith('/api/', $routes[1]->uri);
    }

    public function test_controller_without_route_attribute_still_works(): void
    {
        $this->collector->registerController(PlainController::class);
        
        $routes = $this->collector->getRoutes();
        
        $this->assertCount(1, $routes);
        $this->assertEquals('/plain', $routes[0]->uri);
        $this->assertEquals('plain.index', $routes[0]->name);
    }

    public function test_middleware_is_collected(): void
    {
        $this->collector->registerController(TestController::class);
        
        $routes = $this->collector->getRoutes();
        
        $this->assertIsArray($routes[0]->middleware);
        $this->assertIsArray($routes[1]->middleware);
    }

    public function test_can_get_routes_by_method(): void
    {
        $this->collector->registerController(TestController::class);
        
        $getRoutes = $this->collector->getRoutesByMethod('GET');
        $postRoutes = $this->collector->getRoutesByMethod('POST');
        
        $this->assertCount(1, $getRoutes);
        $this->assertCount(1, $postRoutes);
        $this->assertEquals('GET', $getRoutes[0]->method);
        $this->assertEquals('POST', $postRoutes[0]->method);
    }
}
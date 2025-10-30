<?php

declare(strict_types=1);

namespace Hyperdrive\Tests\Unit\Routing;

use Hyperdrive\Attributes\Get;
use Hyperdrive\Attributes\Route;
use Hyperdrive\Routing\RouteCollector;
use Hyperdrive\Routing\Router;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    private Router $router;
    private RouteCollector $collector;

    protected function setUp(): void
    {
        $this->collector = new RouteCollector();
        $this->router = new Router($this->collector);
    }

    public function test_can_match_simple_route(): void
    {
        // Add a test route
        $this->collector->registerController(SimpleTestController::class);
        
        $match = $this->router->match('GET', '/simple');
        
        $this->assertNotNull($match);
        $this->assertEquals('GET', $match['route']->method);
        $this->assertEquals('/simple', $match['route']->uri);
        $this->assertEquals(SimpleTestController::class, $match['route']->controller);
        $this->assertEquals('index', $match['route']->action);
    }

    public function test_returns_null_for_unmatched_route(): void
    {
        $this->collector->registerController(SimpleTestController::class);
        
        $match = $this->router->match('GET', '/nonexistent');
        
        $this->assertNull($match);
    }

    public function test_can_match_route_with_prefix(): void
    {
        $this->collector->registerController(PrefixTestController::class);
        
        $routes = $this->collector->getRoutes();
        
        $match = $this->router->match('GET', '/api/users');
        
        $this->assertNotNull($match, "Failed to match route /api/users");
        $this->assertEquals('/api/users', $match['route']->uri);
        $this->assertEquals(PrefixTestController::class, $match['route']->controller);
    }

    public function test_can_match_route_with_parameters(): void
    {
        $this->collector->registerController(ParamTestController::class);
        
        $match = $this->router->match('GET', '/users/123');
        
        $this->assertNotNull($match);
        $this->assertEquals('/users/{id}', $match['route']->uri);
        $this->assertEquals(['id' => '123'], $match['parameters']);
    }

    public function test_method_mismatch_returns_null(): void
    {
        $this->collector->registerController(SimpleTestController::class);
        
        $match = $this->router->match('POST', '/simple');
        
        $this->assertNull($match);
    }

    public function test_can_match_multiple_parameter_routes(): void
    {
        $this->collector->registerController(MultiParamTestController::class);
        
        $match = $this->router->match('GET', '/posts/123/comments/456');
        
        $this->assertNotNull($match);
        $this->assertEquals(['postId' => '123', 'commentId' => '456'], $match['parameters']);
    }
    public function test_can_match_root_route(): void
    {
        $this->collector->registerController(RootTestController::class);
        
        $match = $this->router->match('GET', '/');
        
        $this->assertNotNull($match);
        $this->assertEquals('/', $match['route']->uri);
    }

    public function test_can_match_complex_parameter_patterns(): void
    {
        $this->collector->registerController(ComplexParamTestController::class);
        
        $match = $this->router->match('GET', '/categories/5/products/42');
        
        $this->assertNotNull($match);
        $this->assertEquals(['categoryId' => '5', 'productId' => '42'], $match['parameters']);
    }

    public function test_parameter_extraction_ignores_numeric_keys(): void
    {
        $this->collector->registerController(ParamTestController::class);
        
        $match = $this->router->match('GET', '/users/123');
        
        $this->assertNotNull($match);
        $this->assertArrayHasKey('id', $match['parameters']);
        $this->assertArrayNotHasKey(0, $match['parameters']);
        $this->assertArrayNotHasKey(1, $match['parameters']);
    }

}

// Test controller classes
class SimpleTestController
{
    #[Get('/simple', 'simple.index')]
    public function index(): array
    {
        return ['simple' => true];
    }
}

#[Route('/api')]
class PrefixTestController
{
    #[Get('/users', 'users.index')]
    public function index(): array
    {
        return ['users' => []];
    }
}

class ParamTestController
{
    #[Get('/users/{id}', 'users.show')]
    public function show(string $id): array
    {
        return ['user' => ['id' => $id]];
    }
}

class MultiParamTestController
{
    #[Get('/posts/{postId}/comments/{commentId}', 'comments.show')]
    public function show(string $postId, string $commentId): array
    {
        return ['comment' => ['post_id' => $postId, 'id' => $commentId]];
    }
}


// Additional test controllers
class RootTestController
{
    #[Get('/', 'home')]
    public function index(): array
    {
        return ['home' => true];
    }
}

class ComplexParamTestController
{
    #[Get('/categories/{categoryId}/products/{productId}', 'products.show')]
    public function show(string $categoryId, string $productId): array
    {
        return ['product' => ['category_id' => $categoryId, 'id' => $productId]];
    }
}
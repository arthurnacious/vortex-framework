<?php

declare(strict_types=1);

namespace Hyperdrive\Tests\Unit\Attributes;

use Hyperdrive\Attributes\Route;
use Hyperdrive\Attributes\Get;
use Hyperdrive\Attributes\Post;
use Hyperdrive\Attributes\Put;
use Hyperdrive\Attributes\Delete;
use PHPUnit\Framework\TestCase;

class RouteAttributeTest extends TestCase
{
    public function test_route_attribute_can_be_created(): void
    {
        $route = new Route('/api', ['auth']);
        
        $this->assertInstanceOf(Route::class, $route);
        $this->assertEquals('/api', $route->prefix);
        $this->assertEquals(['auth'], $route->middleware);
    }

    public function test_get_attribute_can_be_created(): void
    {
        $get = new Get('/users', 'users.index', ['auth']);
        
        $this->assertInstanceOf(Get::class, $get);
        $this->assertEquals('/users', $get->path);
        $this->assertEquals('users.index', $get->name);
        $this->assertEquals(['auth'], $get->middleware);
    }

    public function test_post_attribute_can_be_created(): void
    {
        $post = new Post('/users', 'users.store', ['auth', 'throttle']);
        
        $this->assertInstanceOf(Post::class, $post);
        $this->assertEquals('/users', $post->path);
        $this->assertEquals('users.store', $post->name);
        $this->assertEquals(['auth', 'throttle'], $post->middleware);
    }

    public function test_put_attribute_can_be_created(): void
    {
        $put = new Put('/users/{id}', 'users.update', ['auth']);
        
        $this->assertInstanceOf(Put::class, $put);
        $this->assertEquals('/users/{id}', $put->path);
        $this->assertEquals('users.update', $put->name);
        $this->assertEquals(['auth'], $put->middleware);
    }

    public function test_delete_attribute_can_be_created(): void
    {
        $delete = new Delete('/users/{id}', 'users.destroy', ['auth']);
        
        $this->assertInstanceOf(Delete::class, $delete);
        $this->assertEquals('/users/{id}', $delete->path);
        $this->assertEquals('users.destroy', $delete->name);
        $this->assertEquals(['auth'], $delete->middleware);
    }

    public function test_attributes_have_correct_targets(): void
    {
        $routeReflection = new \ReflectionClass(Route::class);
        $routeAttribute = $routeReflection->getAttributes()[0];
        
        // Use global namespace for built-in Attribute class
        $this->assertEquals(\Attribute::TARGET_CLASS, $routeAttribute->getArguments()[0]);
        
        $getReflection = new \ReflectionClass(Get::class);
        $getAttribute = $getReflection->getAttributes()[0];
        
        $this->assertEquals(\Attribute::TARGET_METHOD, $getAttribute->getArguments()[0]);
    }
}
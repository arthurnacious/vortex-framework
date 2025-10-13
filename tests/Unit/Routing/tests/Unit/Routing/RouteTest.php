<?php

declare(strict_types=1);

namespace Hyperdrive\Tests\Unit\Routing;

use Hyperdrive\Routing\Route;
use PHPUnit\Framework\TestCase;

class RouteTest extends TestCase
{
    public function test_route_can_be_created(): void
    {
        $route = new Route(
            'GET',
            '/users',
            'UserController',
            'index',
            'users.index',
            ['auth']
        );
        
        $this->assertEquals('GET', $route->method);
        $this->assertEquals('/users', $route->uri);
        $this->assertEquals('UserController', $route->controller);
        $this->assertEquals('index', $route->action);
        $this->assertEquals('users.index', $route->name);
        $this->assertEquals(['auth'], $route->middleware);
    }

    public function test_route_with_minimal_parameters(): void
    {
        $route = new Route(
            'POST',
            '/users',
            'UserController',
            'store'
        );
        
        $this->assertEquals('POST', $route->method);
        $this->assertEquals('/users', $route->uri);
        $this->assertEquals('UserController', $route->controller);
        $this->assertEquals('store', $route->action);
        $this->assertEquals('', $route->name);
        $this->assertEquals([], $route->middleware);
    }
}
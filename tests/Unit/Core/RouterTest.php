<?php

namespace V8\Tests\Unit\Core;

use Symfony\Component\HttpFoundation\Request;
use V8\Tests\TestCase;

class RouterTest extends TestCase
{
    public function test_resolves_basic_route()
    {
        $router = $this->get(\V8\Router::class);
        $router->registerController(\V8\Tests\Stubs\TestController::class);

        $request = Request::create('/test');
        $response = $router->dispatch($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Test response', $response->getContent());
    }
}

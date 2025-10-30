<?php

declare(strict_types=1);

namespace Hyperdrive\Tests\Unit\Http\Middleware;

use Hyperdrive\Contracts\Middleware\MiddlewareInterface;
use Hyperdrive\Http\Request;
use Hyperdrive\Http\Response;
use PHPUnit\Framework\TestCase;

class MiddlewareInterfaceTest extends TestCase
{
    public function test_middleware_interface_has_handle_method(): void
    {
        $methods = get_class_methods(MiddlewareInterface::class);
        $this->assertContains('handle', $methods);
    }

    public function test_middleware_can_be_implemented(): void
    {
        $middleware = new class() implements MiddlewareInterface {
            public function handle(Request $request, \Closure $next): Response
            {
                return $next($request);
            }
        };

        $this->assertInstanceOf(MiddlewareInterface::class, $middleware);
    }
}

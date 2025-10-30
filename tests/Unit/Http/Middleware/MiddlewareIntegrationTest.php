<?php

declare(strict_types=1);

namespace Hyperdrive\Tests\Unit\Http\Middleware;

use Hyperdrive\Http\Middleware\MiddlewarePipeline;
use Hyperdrive\Contracts\Middleware\MiddlewareInterface;
use Hyperdrive\Http\Request;
use Hyperdrive\Http\Response;
use Hyperdrive\Contracts\Container\ContainerInterface;
use Hyperdrive\Container\Container;
use PHPUnit\Framework\TestCase;

class AuthMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, \Closure $next): Response
    {
        $token = $request->getHeader('Authorization');

        if (!$token || $token !== 'Bearer valid-token') {
            return new Response(['error' => 'Unauthorized'], 401);
        }

        $request->setAttribute('user', ['id' => 1, 'name' => 'John Doe']);
        return $next($request);
    }
}

class LoggingMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, \Closure $next): Response
    {
        $request->setAttribute('request_start', microtime(true));

        $response = $next($request);

        $duration = microtime(true) - $request->getAttribute('request_start');
        return $response->withHeader('X-Response-Time', (string) ($duration * 1000));
    }
}

class CorsMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, \Closure $next): Response
    {
        $response = $next($request);

        return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    }
}

class MiddlewareIntegrationTest extends TestCase
{
    public function test_complex_middleware_pipeline(): void
    {
        $pipeline = new MiddlewarePipeline();
        $request = new Request('GET', '/api/users', ['Authorization' => 'Bearer valid-token']);

        $pipeline->pipe(new CorsMiddleware());
        $pipeline->pipe(new LoggingMiddleware());
        $pipeline->pipe(new AuthMiddleware());

        $finalHandler = function (Request $request) {
            $user = $request->getAttribute('user');
            return new Response(['user' => $user, 'data' => 'protected']);
        };

        $response = $pipeline->process($request, $finalHandler);

        $this->assertEquals(200, $response->getStatus());
        $this->assertEquals(['user' => ['id' => 1, 'name' => 'John Doe'], 'data' => 'protected'], $response->getData());

        $headers = $response->getHeaders();
        $this->assertEquals('*', $headers['Access-Control-Allow-Origin'] ?? null);
        $this->assertArrayHasKey('X-Response-Time', $headers);
    }

    public function test_auth_middleware_blocks_unauthorized_requests(): void
    {
        $pipeline = new MiddlewarePipeline();
        $request = new Request('GET', '/api/users'); // No Authorization header

        $pipeline->pipe(new AuthMiddleware());

        $finalHandler = function (Request $request) {
            return new Response(['data' => 'should not reach here']);
        };

        $response = $pipeline->process($request, $finalHandler);

        $this->assertEquals(401, $response->getStatus());
        $this->assertEquals(['error' => 'Unauthorized'], $response->getData());
    }

    public function test_middleware_resolution_with_container(): void
    {
        $container = new Container();
        $container->bind(AuthMiddleware::class, fn() => new AuthMiddleware());

        $pipeline = new MiddlewarePipeline($container);
        $request = new Request('GET', '/api/users', ['Authorization' => 'Bearer valid-token']);

        $pipeline->pipe(AuthMiddleware::class); // String class name

        $finalHandler = function (Request $request) {
            return new Response(['success' => true]);
        };

        $response = $pipeline->process($request, $finalHandler);

        $this->assertEquals(200, $response->getStatus());
        $this->assertTrue($request->getAttribute('user') !== null);
    }
}

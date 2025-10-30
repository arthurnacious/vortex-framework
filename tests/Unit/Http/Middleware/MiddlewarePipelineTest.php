<?php

declare(strict_types=1);

namespace Hyperdrive\Tests\Unit\Http\Middleware;

use Hyperdrive\Http\Middleware\MiddlewarePipeline;
use Hyperdrive\Contracts\Middleware\MiddlewareInterface;
use Hyperdrive\Http\Request;
use Hyperdrive\Http\Response;
use PHPUnit\Framework\TestCase;

class TestMiddleware implements MiddlewareInterface
{
    public function __construct(
        private string $name = 'test',
        private ?string $header = null
    ) {}

    public function handle(Request $request, \Closure $next): Response
    {
        $response = $next($request);

        if ($this->header) {
            $response->withHeader($this->header, $this->name);
        }

        return $response;
    }
}

class ModifyRequestMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, \Closure $next): Response
    {
        $request->setAttribute('modified', true);
        $request->setAttribute('middleware_ran', 'ModifyRequestMiddleware');
        return $next($request);
    }
}

class TerminatingMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, \Closure $next): Response
    {
        // This middleware doesn't call next() - it terminates the request
        return new Response(['terminated' => true], 403);
    }
}

class MiddlewarePipelineTest extends TestCase
{
    private MiddlewarePipeline $pipeline;
    private Request $request;

    protected function setUp(): void
    {
        $this->pipeline = new MiddlewarePipeline();
        $this->request = new Request('GET', '/test');
    }

    public function test_pipeline_can_process_single_middleware(): void
    {
        $finalHandler = function (Request $request) {
            return new Response(['final' => true]);
        };

        $this->pipeline->pipe(new TestMiddleware());

        $response = $this->pipeline->process($this->request, $finalHandler);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(['final' => true], $response->getData());
    }

    public function test_pipeline_processes_middleware_in_order(): void
    {
        $finalHandler = function (Request $request) {
            $middlewareRan = $request->getAttribute('middleware_ran');
            return new Response(['order' => $middlewareRan]);
        };

        $this->pipeline->pipe(new ModifyRequestMiddleware());

        $response = $this->pipeline->process($this->request, $finalHandler);

        $this->assertEquals(['order' => 'ModifyRequestMiddleware'], $response->getData());
        $this->assertTrue($this->request->getAttribute('modified'));
    }

    public function test_pipeline_can_process_multiple_middlewares(): void
    {
        $finalHandler = function (Request $request) {
            return new Response(['final' => true]);
        };

        $this->pipeline->pipe(new TestMiddleware('first', 'X-First'));
        $this->pipeline->pipe(new TestMiddleware('second', 'X-Second'));

        $response = $this->pipeline->process($this->request, $finalHandler);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(['final' => true], $response->getData());
    }

    public function test_middleware_can_terminate_request_early(): void
    {
        $finalHandler = function (Request $request) {
            return new Response(['final' => true]);
        };

        $this->pipeline->pipe(new TerminatingMiddleware());
        $this->pipeline->pipe(new TestMiddleware()); // This should never run

        $response = $this->pipeline->process($this->request, $finalHandler);

        $this->assertEquals(403, $response->getStatus());
        $this->assertEquals(['terminated' => true], $response->getData());
    }

    public function test_pipeline_works_with_empty_middlewares(): void
    {
        $finalHandler = function (Request $request) {
            return new Response(['final' => true]);
        };

        $response = $this->pipeline->process($this->request, $finalHandler);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(['final' => true], $response->getData());
    }

    public function test_can_add_middleware_instances(): void
    {
        $this->pipeline->pipe(new TestMiddleware());

        $middlewares = $this->pipeline->getMiddlewares();

        $this->assertCount(1, $middlewares);
        $this->assertInstanceOf(TestMiddleware::class, $middlewares[0]);
    }

    public function test_can_add_middleware_classes(): void
    {
        $this->pipeline->pipe(TestMiddleware::class);

        $middlewares = $this->pipeline->getMiddlewares();

        $this->assertCount(1, $middlewares);
        $this->assertEquals(TestMiddleware::class, $middlewares[0]);
    }

    public function test_middleware_class_is_resolved_during_processing(): void
    {
        $finalHandler = function (Request $request) {
            return new Response(['final' => true]);
        };

        $this->pipeline->pipe(TestMiddleware::class); // Add as class name

        $response = $this->pipeline->process($this->request, $finalHandler);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(['final' => true], $response->getData());
    }

    public function test_middleware_can_modify_response(): void
    {
        $finalHandler = function (Request $request) {
            return new Response(['data' => 'original']);
        };

        $this->pipeline->pipe(new class() implements MiddlewareInterface {
            public function handle(Request $request, \Closure $next): Response
            {
                $response = $next($request);
                return $response->withHeader('X-Modified', 'true');
            }
        });

        $response = $this->pipeline->process($this->request, $finalHandler);

        $this->assertEquals('true', $response->getHeaders()['X-Modified'] ?? null);
    }
}

<?php

declare(strict_types=1);

namespace Hyperdrive\Tests\Unit\Http;

use Hyperdrive\Http\ControllerResolver;
use Hyperdrive\Container\Container;
use Hyperdrive\Http\Request;
use Hyperdrive\Http\Response;
use Hyperdrive\Http\Controller;
use Hyperdrive\Routing\Route;
use PHPUnit\Framework\TestCase;

class SimpleTestController extends Controller
{
    public function index(): Response
    {
        return $this->json(['message' => 'Hello World']);
    }

    public function show(int $id): Response
    {
        return $this->json(['id' => $id]);
    }

    public function create(Request $request): Response
    {
        return $this->json(['data' => $request->getData()]);
    }
}

class DependencyTestController extends Controller
{
    public function __construct(
        private \stdClass $dependency
    ) {}

    public function index(): Response
    {
        return $this->json(['dependency' => $this->dependency instanceof \stdClass]);
    }
}

class ControllerResolverTest extends TestCase
{
    private ControllerResolver $resolver;
    private Container $container;

    protected function setUp(): void
    {
        $this->resolver = new ControllerResolver();
        $this->container = new Container();
    }

    public function test_can_resolve_controller_method(): void
    {
        $route = new Route(
            'GET',
            '/test',
            SimpleTestController::class,
            'index'
        );

        $request = new Request();

        $response = $this->resolver->resolve($route, $request, $this->container);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(['message' => 'Hello World'], $response->getData());
    }

    public function test_can_inject_route_parameters(): void
    {
        $route = new Route(
            'GET',
            '/users/{id}',
            SimpleTestController::class,
            'show'
        );

        $request = new Request();

        $response = $this->resolver->resolve($route, $request, $this->container, ['id' => '123']);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(['id' => 123], $response->getData()); // Should be converted to int
    }

    public function test_can_inject_request_object(): void
    {
        $route = new Route(
            'POST',
            '/users',
            SimpleTestController::class,
            'create'
        );

        $requestData = ['name' => 'John', 'email' => 'john@example.com'];
        $request = new Request('POST', '/users');
        $request->setData($requestData);

        $response = $this->resolver->resolve($route, $request, $this->container);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(['data' => $requestData], $response->getData());
    }

    public function test_can_resolve_controller_with_dependencies(): void
    {
        $route = new Route(
            'GET',
            '/dependency',
            DependencyTestController::class,
            'index'
        );

        // Register the dependency in the container
        $this->container->bind(\stdClass::class, fn() => new \stdClass());

        $request = new Request();

        $response = $this->resolver->resolve($route, $request, $this->container);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(['dependency' => true], $response->getData());
    }

    public function test_throws_exception_for_invalid_controller(): void
    {
        $route = new Route(
            'GET',
            '/invalid',
            'NonExistentController',
            'index'
        );

        $request = new Request();

        $this->expectException(\InvalidArgumentException::class);
        $this->resolver->resolve($route, $request, $this->container);
    }

    public function test_throws_exception_for_invalid_method(): void
    {
        $route = new Route(
            'GET',
            '/invalid',
            SimpleTestController::class,
            'nonExistentMethod'
        );

        $request = new Request();

        $this->expectException(\InvalidArgumentException::class);
        $this->resolver->resolve($route, $request, $this->container);
    }
}

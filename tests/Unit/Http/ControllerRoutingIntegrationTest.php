<?php

declare(strict_types=1);

namespace Hyperdrive\Tests\Unit\Http;

use Hyperdrive\Routing\RouteCollector;
use Hyperdrive\Routing\Router;
use Hyperdrive\Http\ControllerResolver;
use Hyperdrive\Http\Request;
use Hyperdrive\Contracts\Container\ContainerInterface;
use Hyperdrive\Attributes\Get;
use Hyperdrive\Attributes\Post;
use Hyperdrive\Attributes\Route;
use Hyperdrive\Http\Controller;
use Hyperdrive\Http\Response;
use PHPUnit\Framework\TestCase;

// Test controllers for integration
class UserController extends Controller
{
    #[Get('/users', 'users.index')]
    public function index(): Response
    {
        return $this->json(['users' => []]);
    }

    #[Get('/users/{id}', 'users.show')]
    public function show(int $id): Response
    {
        return $this->json(['user' => ['id' => $id]]);
    }

    #[Post('/users', 'users.create')]
    public function create(Request $request): Response
    {
        return $this->json(['created' => $request->getData()], 201);
    }
}

#[Route('/api')]
class ApiController extends Controller
{
    #[Get('/status', 'api.status')]
    public function status(): Response
    {
        return $this->json(['status' => 'OK']);
    }
}

class ControllerRoutingIntegrationTest extends TestCase
{
    private Router $router;
    private ControllerResolver $resolver;
    private ContainerInterface $container;

    protected function setUp(): void
    {
        $collector = new RouteCollector();
        $this->router = new Router($collector);
        $this->resolver = new ControllerResolver();
        $this->container = $this->createMock(ContainerInterface::class);
    }

    public function test_can_route_and_resolve_controller(): void
    {
        $this->router->getCollector()->registerController(UserController::class);

        $match = $this->router->match('GET', '/users/123');

        $this->assertNotNull($match);

        $request = new Request('GET', '/users/123');
        $response = $this->resolver->resolve(
            $match['route'],
            $request,
            $this->container,
            $match['parameters']
        );

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(['user' => ['id' => 123]], $response->getData());
    }

    public function test_can_handle_controller_with_request_injection(): void
    {
        $this->router->getCollector()->registerController(UserController::class);

        $match = $this->router->match('POST', '/users');

        $this->assertNotNull($match);

        $requestData = ['name' => 'John', 'email' => 'john@example.com'];
        $request = new Request('POST', '/users');
        $request->setData($requestData);

        $response = $this->resolver->resolve(
            $match['route'],
            $request,
            $this->container,
            $match['parameters']
        );

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(201, $response->getStatus());
        $this->assertEquals(['created' => $requestData], $response->getData());
    }

    public function test_can_handle_prefixed_controllers(): void
    {
        $this->router->getCollector()->registerController(ApiController::class);

        $match = $this->router->match('GET', '/api/status');

        $this->assertNotNull($match);

        $request = new Request('GET', '/api/status');
        $response = $this->resolver->resolve(
            $match['route'],
            $request,
            $this->container,
            $match['parameters']
        );

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(['status' => 'OK'], $response->getData());
    }

    public function test_parameter_type_conversion_works(): void
    {
        $this->router->getCollector()->registerController(UserController::class);

        $match = $this->router->match('GET', '/users/456');

        $this->assertNotNull($match);
        $this->assertEquals(['id' => '456'], $match['parameters']);

        $request = new Request('GET', '/users/456');
        $response = $this->resolver->resolve(
            $match['route'],
            $request,
            $this->container,
            $match['parameters']
        );

        $data = $response->getData();
        $this->assertEquals(456, $data['user']['id']); // Should be integer, not string
    }
}

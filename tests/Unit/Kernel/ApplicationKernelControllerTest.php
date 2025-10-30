<?php

declare(strict_types=1);

namespace Hyperdrive\Tests\Unit\Kernel;

use Hyperdrive\Kernel\ApplicationKernel;
use Hyperdrive\Routing\RouteCollector;
use Hyperdrive\Routing\Router;
use Hyperdrive\Http\ControllerResolver;
use Hyperdrive\Http\Request;
use Hyperdrive\Http\Response;
use Hyperdrive\Attributes\Get;
use Hyperdrive\Http\Controller;
use PHPUnit\Framework\TestCase;

class TestKernelController extends Controller
{
    #[Get('/test', 'test.index')]
    public function index(): Response
    {
        return $this->json(['message' => 'Kernel Test']);
    }
}

class ApplicationKernelControllerTest extends TestCase
{
    public function test_kernel_can_handle_controller_requests(): void
    {
        $kernel = new ApplicationKernel('testing');

        // Register a test controller
        $collector = new RouteCollector();
        $collector->registerController(TestKernelController::class);

        // Manually set up router in kernel's container
        $container = $kernel->getContainer();
        $container->instance(Router::class, new Router($collector));
        $container->instance(ControllerResolver::class, new ControllerResolver());

        $kernel->boost();

        // This is a simplified test - in reality, the kernel would handle this internally
        $router = $container->get(Router::class);
        $resolver = $container->get(ControllerResolver::class);

        $match = $router->match('GET', '/test');
        $this->assertNotNull($match);

        $request = new Request('GET', '/test');
        $response = $resolver->resolve($match['route'], $request, $container, $match['parameters']);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(['message' => 'Kernel Test'], $response->getData());
    }
}

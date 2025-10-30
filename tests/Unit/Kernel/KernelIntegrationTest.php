<?php

declare(strict_types=1);

namespace Hyperdrive\Tests\Unit\Kernel;

use Hyperdrive\Kernel\ApplicationKernel;
use Hyperdrive\Contracts\Module\ModuleInterface;
use Hyperdrive\Contracts\Container\ContainerInterface;
use Hyperdrive\Attributes\Module;
use Hyperdrive\Attributes\Get;
use Hyperdrive\Http\Controller;
use Hyperdrive\Http\Response;
use PHPUnit\Framework\TestCase;

#[Module(name: 'TestModule')]
class TestIntegrationModule implements ModuleInterface
{
    public function register(ContainerInterface $container): void {}

    public function boot(ContainerInterface $container): void {}

    public function getControllers(): array
    {
        return [TestIntegrationController::class];
    }

    public function getProviders(): array
    {
        return [];
    }

    public function getMiddlewares(): array
    {
        return [];
    }
}

class TestIntegrationController extends Controller
{
    #[Get('/test', 'test.index')]
    public function index(): Response
    {
        return $this->json(['message' => 'Integration working!']);
    }
}

class KernelIntegrationTest extends TestCase
{
    public function test_kernel_can_handle_integrated_request(): void
    {
        $kernel = new ApplicationKernel('testing');
        $kernel->registerModule(TestIntegrationModule::class);
        $kernel->boost();

        // Create a mock request
        $request = new \Hyperdrive\Http\Request('GET', '/test');

        // Use reflection to call handleRequest directly
        $reflection = new \ReflectionClass($kernel);
        $method = $reflection->getMethod('handleRequest');
        $method->setAccessible(true);

        $response = $method->invoke($kernel, $request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(['message' => 'Integration working!'], $response->getData());
    }

    public function test_kernel_registers_modules_and_routes(): void
    {
        $kernel = new ApplicationKernel('testing');
        $kernel->registerModule(TestIntegrationModule::class);
        $kernel->boost();

        $router = $kernel->getRouter();
        $match = $router->match('GET', '/test');

        $this->assertNotNull($match);
        $this->assertEquals(TestIntegrationController::class, $match['route']->controller);
        $this->assertEquals('index', $match['route']->action);
    }
}

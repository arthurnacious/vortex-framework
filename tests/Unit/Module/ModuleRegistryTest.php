<?php

declare(strict_types=1);

namespace Hyperdrive\Tests\Unit\Module;

use Hyperdrive\Module\ModuleRegistry;
use Hyperdrive\Contracts\Module\ModuleInterface;
use Hyperdrive\Contracts\Container\ContainerInterface;
use Hyperdrive\Attributes\Module;
use PHPUnit\Framework\TestCase;

// Test module classes
#[Module(name: 'TestModule', version: '1.0.0')]
class TestModule implements ModuleInterface
{
    public function register(ContainerInterface $container): void
    {
        $container->bind('test.service', \stdClass::class);
    }

    public function boot(ContainerInterface $container): void
    {
        // Boot logic
    }

    public function getControllers(): array
    {
        return ['TestController'];
    }

    public function getProviders(): array
    {
        return ['TestProvider'];
    }

    public function getMiddlewares(): array
    {
        return ['test' => 'TestMiddleware'];
    }
}

class ModuleRegistryTest extends TestCase
{
    private ModuleRegistry $registry;
    private ContainerInterface $container;

    protected function setUp(): void
    {
        $this->registry = new ModuleRegistry();
        $this->container = $this->createMock(ContainerInterface::class);
    }

    public function test_can_register_module(): void
    {
        $this->registry->register(TestModule::class, $this->container);

        $modules = $this->registry->getModules();
        $this->assertArrayHasKey(TestModule::class, $modules);
    }

    public function test_module_is_instantiated_on_registration(): void
    {
        $this->container->expects($this->once())
            ->method('bind')
            ->with('test.service', \stdClass::class);

        $this->registry->register(TestModule::class, $this->container);
    }

    public function test_can_get_module_metadata(): void
    {
        $this->registry->register(TestModule::class, $this->container);

        $metadata = $this->registry->getModuleMetadata(TestModule::class);

        $this->assertNotNull($metadata);
        $this->assertEquals('TestModule', $metadata['name']);
        $this->assertEquals('1.0.0', $metadata['version']);
    }

    public function test_can_get_all_controllers(): void
    {
        $this->registry->register(TestModule::class, $this->container);

        $controllers = $this->registry->getAllControllers();

        $this->assertContains('TestController', $controllers);
    }

    public function test_can_get_all_providers(): void
    {
        $this->registry->register(TestModule::class, $this->container);

        $providers = $this->registry->getAllProviders();

        $this->assertContains('TestProvider', $providers);
    }

    public function test_can_get_all_middlewares(): void
    {
        $this->registry->register(TestModule::class, $this->container);

        $middlewares = $this->registry->getAllMiddlewares();

        $this->assertEquals(['test' => 'TestMiddleware'], $middlewares);
    }

    public function test_throws_exception_for_invalid_module(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->registry->register('NonExistentModule', $this->container);
    }

    public function test_throws_exception_for_non_module_class(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->registry->register(\stdClass::class, $this->container);
    }
}

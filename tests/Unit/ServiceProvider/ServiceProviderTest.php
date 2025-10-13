<?php

declare(strict_types=1);

namespace Hyperdrive\Tests\Unit\ServiceProvider;

use Hyperdrive\ServiceProvider\ServiceProvider;
use Hyperdrive\Contracts\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;

class ServiceProviderTest extends TestCase
{
    public function test_service_provider_can_be_instantiated(): void
    {
        $provider = new class() extends ServiceProvider {};
        
        $this->assertInstanceOf(ServiceProvider::class, $provider);
    }

    public function test_service_provider_has_default_methods(): void
    {
        $provider = new class() extends ServiceProvider {};
        $container = $this->createMock(ContainerInterface::class);
        
        // Should not throw errors
        $provider->register($container);
        $provider->boot($container);
        
        $this->assertIsArray($provider->provides());
        $this->assertFalse($provider->isDeferred());
    }

    public function test_service_provider_can_register_services(): void
    {
        $provider = new class() extends ServiceProvider {
            public function register(ContainerInterface $container): void
            {
                $container->bind('test.service', \stdClass::class);
            }
        };
        
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
                 ->method('bind')
                 ->with('test.service', \stdClass::class);
        
        $provider->register($container);
    }
}
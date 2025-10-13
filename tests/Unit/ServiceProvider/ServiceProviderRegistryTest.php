<?php

declare(strict_types=1);

namespace Hyperdrive\Tests\Unit\ServiceProvider;

use Hyperdrive\ServiceProvider\ServiceProviderRegistry;
use Hyperdrive\ServiceProvider\ServiceProvider;
use Hyperdrive\Contracts\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;

class ServiceProviderRegistryTest extends TestCase
{
    private ServiceProviderRegistry $registry;
    private ContainerInterface $container;

    protected function setUp(): void
    {
        $this->registry = new ServiceProviderRegistry();
        $this->container = $this->createMock(ContainerInterface::class);
    }

    public function test_can_register_service_provider(): void
    {
        $providerClass = get_class(new class() extends ServiceProvider {});
        
        $this->registry->register($providerClass, $this->container);
        
        $providers = $this->registry->getProviders();
        $this->assertArrayHasKey($providerClass, $providers);
    }

    public function test_throws_exception_for_invalid_provider(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $this->registry->register('NonExistentProvider');
    }

    public function test_throws_exception_for_non_service_provider(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $this->registry->register(\stdClass::class);
    }

    public function test_can_boot_providers(): void
    {
        $providerClass = get_class(new class() extends ServiceProvider {
            public function boot(ContainerInterface $container): void {
                // Simple boot implementation
            }
        });
        
        $this->registry->register($providerClass, $this->container);
        $this->registry->boot($this->container);
        
        $this->assertContains($providerClass, $this->registry->getBooted());
    }

    public function test_deferred_provider_is_not_registered_immediately(): void
    {
        // Use a static variable to track registration
        $GLOBALS['test_registered'] = false;
        
        $providerClass = get_class(new class() extends ServiceProvider {
            public function register(ContainerInterface $container): void { 
                $GLOBALS['test_registered'] = true; 
            }
            public function isDeferred(): bool { return true; }
        });
        
        $this->registry->register($providerClass, $this->container);
        
        $this->assertFalse($GLOBALS['test_registered'], 'Deferred provider should not register immediately');
        $this->assertArrayHasKey($providerClass, $this->registry->getDeferred());
        
        // Cleanup
        unset($GLOBALS['test_registered']);
    }

    public function test_provider_can_provide_services(): void
    {
        $providerClass = get_class(new class() extends ServiceProvider {
            public function provides(): array { return ['service.a', 'service.b']; }
        });
        
        $this->registry->register($providerClass, $this->container);
        
        $this->assertArrayHasKey($providerClass, $this->registry->getProviders());
    }

    public function test_deferred_provider_can_be_loaded_later(): void
    {
        $GLOBALS['test_registered'] = false;
        $GLOBALS['test_booted'] = false;
        
        $providerClass = get_class(new class() extends ServiceProvider {
            public function register(ContainerInterface $container): void { 
                $GLOBALS['test_registered'] = true; 
            }
            public function boot(ContainerInterface $container): void { 
                $GLOBALS['test_booted'] = true; 
            }
            public function isDeferred(): bool { return true; }
            public function provides(): array { return ['some.service']; }
        });
        
        $this->registry->register($providerClass, $this->container);
        
        // Should not be registered or booted yet
        $this->assertFalse($GLOBALS['test_registered']);
        $this->assertFalse($GLOBALS['test_booted']);
        
        // Load the deferred provider
        $this->registry->loadDeferred('some.service');
        
        // Should now be registered and booted
        $this->assertTrue($GLOBALS['test_registered']);
        $this->assertTrue($GLOBALS['test_booted']);
        
        // Cleanup
        unset($GLOBALS['test_registered'], $GLOBALS['test_booted']);
    }
}
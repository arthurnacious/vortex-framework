<?php

declare(strict_types=1);

namespace Hyperdrive\Tests\Unit\Module;

use Hyperdrive\Contracts\Module\ModuleInterface;
use Hyperdrive\Contracts\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;

class ModuleInterfaceTest extends TestCase
{
    public function test_module_interface_has_required_methods(): void
    {
        $methods = get_class_methods(ModuleInterface::class);

        $this->assertContains('register', $methods);
        $this->assertContains('boot', $methods);
        $this->assertContains('getControllers', $methods);
        $this->assertContains('getProviders', $methods);
        $this->assertContains('getMiddlewares', $methods);
    }

    public function test_module_can_be_implemented(): void
    {
        $module = new class() implements ModuleInterface {
            public function register(ContainerInterface $container): void {}
            public function boot(ContainerInterface $container): void {}
            public function getControllers(): array
            {
                return [];
            }
            public function getProviders(): array
            {
                return [];
            }
            public function getMiddlewares(): array
            {
                return [];
            }
        };

        $this->assertInstanceOf(ModuleInterface::class, $module);
    }
}

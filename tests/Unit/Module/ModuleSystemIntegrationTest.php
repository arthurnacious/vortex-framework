<?php

declare(strict_types=1);

namespace Hyperdrive\Tests\Unit\Module;

use Hyperdrive\Module\ModuleRegistry;
use Hyperdrive\Contracts\Module\ModuleInterface;
use Hyperdrive\Contracts\Container\ContainerInterface;
use Hyperdrive\Attributes\Module;
use Hyperdrive\Container\Container;
use PHPUnit\Framework\TestCase;

#[Module(name: 'Auth', version: '1.0.0', imports: [], providers: ['AuthService'])]
class AuthModule implements ModuleInterface
{
    public function register(ContainerInterface $container): void
    {
        $container->bind('auth.service', fn() => new \stdClass());
    }

    public function boot(ContainerInterface $container): void
    {
        // Boot logic
    }

    public function getControllers(): array
    {
        return ['AuthController'];
    }

    public function getProviders(): array
    {
        return ['AuthService'];
    }

    public function getMiddlewares(): array
    {
        return ['auth' => 'JwtMiddleware'];
    }
}

#[Module(name: 'User', version: '1.0.0', imports: ['Auth'], providers: ['UserService'])]
class UserModule implements ModuleInterface
{
    public function register(ContainerInterface $container): void
    {
        $container->bind('user.service', fn() => new \stdClass());
    }

    public function boot(ContainerInterface $container): void
    {
        // Boot logic
    }

    public function getControllers(): array
    {
        return ['UserController'];
    }

    public function getProviders(): array
    {
        return ['UserService'];
    }

    public function getMiddlewares(): array
    {
        return ['user.owner' => 'UserOwnershipMiddleware'];
    }
}

class ModuleSystemIntegrationTest extends TestCase
{
    public function test_multiple_modules_can_be_registered(): void
    {
        $registry = new ModuleRegistry();
        $container = new Container();

        $registry->register(AuthModule::class, $container);
        $registry->register(UserModule::class, $container);

        $this->assertCount(2, $registry->getModules());
        $this->assertTrue($container->has('auth.service'));
        $this->assertTrue($container->has('user.service'));
    }

    public function test_modules_can_be_booted(): void
    {
        $registry = new ModuleRegistry();
        $container = new Container();

        $registry->register(AuthModule::class, $container);
        $registry->register(UserModule::class, $container);

        // Should not throw an exception
        $registry->boot($container);

        $this->assertTrue(true); // If we reach here, boot succeeded
    }

    public function test_can_aggregate_controllers_from_multiple_modules(): void
    {
        $registry = new ModuleRegistry();
        $container = $this->createMock(ContainerInterface::class);

        $registry->register(AuthModule::class, $container);
        $registry->register(UserModule::class, $container);

        $controllers = $registry->getAllControllers();

        $this->assertContains('AuthController', $controllers);
        $this->assertContains('UserController', $controllers);
        $this->assertCount(2, $controllers);
    }

    public function test_can_aggregate_middlewares_from_multiple_modules(): void
    {
        $registry = new ModuleRegistry();
        $container = $this->createMock(ContainerInterface::class);

        $registry->register(AuthModule::class, $container);
        $registry->register(UserModule::class, $container);

        $middlewares = $registry->getAllMiddlewares();

        $this->assertEquals([
            'auth' => 'JwtMiddleware',
            'user.owner' => 'UserOwnershipMiddleware'
        ], $middlewares);
    }

    public function test_module_metadata_is_correctly_extracted(): void
    {
        $registry = new ModuleRegistry();
        $container = $this->createMock(ContainerInterface::class);

        $registry->register(UserModule::class, $container);

        $metadata = $registry->getModuleMetadata(UserModule::class);

        $this->assertNotNull($metadata);
        $this->assertEquals('User', $metadata['name']);
        $this->assertEquals('1.0.0', $metadata['version']);
        $this->assertEquals(['Auth'], $metadata['imports']);
        $this->assertEquals(['UserService'], $metadata['providers']);
    }
}

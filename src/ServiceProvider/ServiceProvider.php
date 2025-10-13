<?php

declare(strict_types=1);

namespace Hyperdrive\ServiceProvider;

use Hyperdrive\Contracts\ServiceProvider\ServiceProviderInterface;
use Hyperdrive\Contracts\Container\ContainerInterface;

abstract class ServiceProvider implements ServiceProviderInterface
{
    public function register(ContainerInterface $container): void
    {
        // Base implementation - can be overridden
    }

    public function boot(ContainerInterface $container): void
    {
        // Base implementation - can be overridden
    }

    public function provides(): array
    {
        return [];
    }

    public function isDeferred(): bool
    {
        return false;
    }
}
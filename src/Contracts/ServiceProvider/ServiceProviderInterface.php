<?php

declare(strict_types=1);

namespace Hyperdrive\Contracts\ServiceProvider;

use Hyperdrive\Contracts\Container\ContainerInterface;

interface ServiceProviderInterface
{
    public function register(ContainerInterface $container): void;
    public function boot(ContainerInterface $container): void;
    public function provides(): array;
    public function isDeferred(): bool;
}
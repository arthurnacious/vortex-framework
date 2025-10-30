<?php

declare(strict_types=1);

namespace Hyperdrive\Contracts\Module;

use Hyperdrive\Contracts\Container\ContainerInterface;

interface ModuleInterface
{
    public function register(ContainerInterface $container): void;
    public function boot(ContainerInterface $container): void;
    public function getControllers(): array;
    public function getProviders(): array;
    public function getMiddlewares(): array;
}

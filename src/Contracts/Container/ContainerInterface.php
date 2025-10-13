<?php

declare(strict_types=1);

namespace Hyperdrive\Contracts\Container;

use Psr\Container\ContainerInterface as PsrContainerInterface;

interface ContainerInterface extends PsrContainerInterface
{
    public function bind(string $abstract, $concrete = null): void;
    public function singleton(string $abstract, $concrete = null): void;
    public function instance(string $abstract, object $instance): void;
    public function bound(string $abstract): bool;
}
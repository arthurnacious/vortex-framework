<?php

declare(strict_types=1);

namespace Hyperdrive\Contracts\Kernel;

use Hyperdrive\Http\Response;

interface KernelInterface
{
    public function boost(): void;
    public function handle(): Response;
    public function getBootTimeMs(): float;
    public function getResponseTimeMs(): float;
    public function getEnvironment(): string;
    public function isBootstrapped(): bool;
}
<?php

declare(strict_types=1);

namespace Hyperdrive;

use Hyperdrive\Kernel\ApplicationKernel;
use Hyperdrive\Http\Response;

class Hyperdrive
{
    private ApplicationKernel $kernel;
    
    public function __construct(string $environment = 'production')
    {
        $this->kernel = new ApplicationKernel($environment);
    }
    
    public static function boost(string $environment = 'production'): self
    {
        $instance = new self($environment);
        $instance->kernel->boost();
        return $instance;
    }
    
    public function warp(): Response
    {
        return $this->kernel->handle();
    }
    
    public function getKernel(): ApplicationKernel
    {
        return $this->kernel;
    }

    public function getEngine(): string
    {
        return $this->kernel->getEngine();
    }
    
}
<?php

declare(strict_types=1);

namespace Hyperdrive\Kernel;

use Hyperdrive\Contracts\Kernel\KernelInterface;
use Hyperdrive\Http\Response;

class ApplicationKernel implements KernelInterface
{
    private float $frameworkStartTime;
    private float $frameworkBootTime = 0.0;
    private float $requestStartTime = 0.0;
    private bool $bootstrapped = false;
    private string $engine;
    
    public function __construct(
        private string $environment = 'production'
    ) {
        $this->frameworkStartTime = microtime(true);
        $this->engine = $this->detectEngine();
    }
    
    public function boost(): void
    {
        if ($this->bootstrapped) {
            return;
        }
        
        // Bootstrap the kernel
        $this->frameworkBootTime = microtime(true);
        $this->bootstrapped = true;
    }
    
    public function handle(): Response
    {
        $this->requestStartTime = microtime(true);
        
        // Handle the request and generate response
        $responseTime = round((microtime(true) - $this->requestStartTime) * 1000, 2);
        
        $data = [
            'message' => '🚀 Kernel handling request...',
            'environment' => $this->environment,
            'engine' => $this->engine,
            'response_time_ms' => $responseTime,
            'boot_time_ms' => $this->getBootTimeMs()
        ];
        
        return Response::json($data);
    }
    
    public function getBootTimeMs(): float
    {
        if ($this->frameworkBootTime === 0.0) {
            return 0.0;
        }
        return ($this->frameworkBootTime - $this->frameworkStartTime) * 1000;
    }
    
    public function getResponseTimeMs(): float
    {
        if ($this->requestStartTime === 0.0) {
            return 0.0;
        }
        return (microtime(true) - $this->requestStartTime) * 1000;
    }
    
    public function getEnvironment(): string
    {
        return $this->environment;
    }
    
    public function isBootstrapped(): bool
    {
        return $this->bootstrapped;
    }
    
    public function getEngine(): string
    {
        return $this->engine;
    }
    
    private function detectEngine(): string
    {
        if (extension_loaded('openswoole')) {
            return 'openswoole';
        }
        
        if (extension_loaded('swoole')) {
            return 'swoole'; 
        }
        
        return 'roadster';
    }
}
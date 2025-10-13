<?php

declare(strict_types=1);

namespace Hyperdrive;

use Hyperdrive\Http\Response;

class Hyperdrive
{
    private float $startTime;
    private string $engine;
    
    public function __construct()
    {
        $this->startTime = microtime(true);
        $this->engine = $this->detectEngine();
    }
    
    public static function boost(): string 
    {
        return "🚀 Hyperdrive boosted!";
    }
    
    public function warp(): Response 
    {
        $responseTime = round((microtime(true) - $this->startTime) * 1000, 2);
        
        $data = [
            'message' => '⚡ Warping to lightspeed...',
            'engine' => $this->engine,
            'response_time_ms' => $responseTime
        ];
        
        return Response::json($data);
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
    
    public function getEngine(): string
    {
        return $this->engine;
    }
}
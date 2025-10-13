<?php

declare(strict_types=1);

namespace Hyperdrive;

class Hyperdrive
{
    private float $startTime;
    
    public function __construct()
    {
        $this->startTime = microtime(true);
    }
    
    public static function boost(): string 
    {
        return "🚀 Hyperdrive boosted!";
    }
    
    public function warp(): array 
    {
        $responseTime = round((microtime(true) - $this->startTime) * 1000, 2);
        
        return [
            'message' => '⚡ Warping to lightspeed...',
            'response_time_ms' => $responseTime
        ];
    }
}
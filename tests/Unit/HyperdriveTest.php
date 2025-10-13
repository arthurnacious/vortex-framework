<?php

declare(strict_types=1);

namespace Hyperdrive\Tests\Unit;

use Hyperdrive\Hyperdrive;
use PHPUnit\Framework\TestCase;

class HyperdriveTest extends TestCase
{
    public function test_boost_returns_message(): void
    {
        $this->assertEquals('🚀 Hyperdrive boosted!', Hyperdrive::boost());
    }
    
    public function test_warp_returns_timing_data(): void 
    {
        $hyperdrive = new Hyperdrive();
        $result = $hyperdrive->warp();
        
        $this->assertIsArray($result);
        $this->assertEquals('⚡ Warping to lightspeed...', $result['message']);
        $this->assertIsFloat($result['response_time_ms']);
    }
}
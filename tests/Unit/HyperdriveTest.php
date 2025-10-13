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
    
    public function test_warp_returns_timing_and_engine_data(): void 
    {
        $hyperdrive = new Hyperdrive();
        $result = $hyperdrive->warp();
        
        $this->assertIsArray($result);
        $this->assertEquals('⚡ Warping to lightspeed...', $result['message']);
        $this->assertIsFloat($result['response_time_ms']);
        $this->assertContains($result['engine'], ['openswoole', 'swoole', 'roadster']);
    }
    
    public function test_can_get_engine_directly(): void
    {
        $hyperdrive = new Hyperdrive();
        $engine = $hyperdrive->getEngine();
        
        $this->assertContains($engine, ['openswoole', 'swoole', 'roadster']);
    }
}
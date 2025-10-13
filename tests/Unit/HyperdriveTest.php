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
    
    public function test_warp_returns_string(): void 
    {
        $hyperdrive = new Hyperdrive();
        $result = $hyperdrive->warp();
        
        // Just test it returns a string for now
        $this->assertIsString($result);
    }
}
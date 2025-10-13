<?php

declare(strict_types=1);

namespace Hyperdrive\Tests\Unit;

use Hyperdrive\Hyperdrive;
use Hyperdrive\Http\Response;
use PHPUnit\Framework\TestCase;

class HyperdriveTest extends TestCase
{
    public function test_boost_returns_message(): void
    {
        $this->assertEquals('🚀 Hyperdrive boosted!', Hyperdrive::boost());
    }
    
    public function test_warp_returns_response_object(): void 
    {
        $hyperdrive = new Hyperdrive();
        $response = $hyperdrive->warp();
        
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatus());
        $this->assertEquals(['Content-Type' => 'application/json; charset=utf-8'], $response->getHeaders());
    }
    
    public function test_response_contains_correct_data(): void
    {
        $hyperdrive = new Hyperdrive();
        $response = $hyperdrive->warp();
        $data = $response->getData();
        
        $this->assertEquals('⚡ Warping to lightspeed...', $data['message']);
        $this->assertIsFloat($data['response_time_ms']);
        $this->assertContains($data['engine'], ['openswoole', 'swoole', 'roadster']);
    }
    
    public function test_can_get_engine_directly(): void
    {
        $hyperdrive = new Hyperdrive();
        $engine = $hyperdrive->getEngine();
        
        $this->assertContains($engine, ['openswoole', 'swoole', 'roadster']);
    }
}
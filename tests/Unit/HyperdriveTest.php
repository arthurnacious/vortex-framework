<?php

declare(strict_types=1);

namespace Hyperdrive\Tests\Unit;

use Hyperdrive\Hyperdrive;
use Hyperdrive\Http\Response;
use PHPUnit\Framework\TestCase;

class HyperdriveTest extends TestCase
{
    public function test_boost_returns_hyperdrive_instance(): void
    {
        $hyperdrive = Hyperdrive::boost();
        $this->assertInstanceOf(Hyperdrive::class, $hyperdrive);
    }

    public function test_warp_returns_response_object(): void
    {
        $hyperdrive = Hyperdrive::boost();
        $response = $hyperdrive->warp();

        $this->assertInstanceOf(Response::class, $response);

        $this->assertEquals(404, $response->getStatus());
    }

    public function test_response_contains_correct_data(): void
    {
        $hyperdrive = Hyperdrive::boost();
        $response = $hyperdrive->warp();
        $data = $response->getData();

        $this->assertIsArray($data);
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('Route not found', $data['error']);
    }

    public function test_can_get_kernel(): void
    {
        $hyperdrive = Hyperdrive::boost();
        $kernel = $hyperdrive->getKernel();

        $this->assertTrue($kernel->isBootstrapped());
        $this->assertIsFloat($kernel->getBootTimeMs());
    }

    public function test_can_get_engine(): void
    {
        $hyperdrive = Hyperdrive::boost();
        $engine = $hyperdrive->getEngine();

        $this->assertContains($engine, ['openswoole', 'swoole', 'roadster']);
    }
}

<?php

declare(strict_types=1);

namespace Hyperdrive\Tests\Unit\Kernel;

use Hyperdrive\Kernel\ApplicationKernel;
use Hyperdrive\Http\Response;
use PHPUnit\Framework\TestCase;

class ApplicationKernelTest extends TestCase
{
    public function test_kernel_can_be_bootstrapped(): void
    {
        $kernel = new ApplicationKernel('testing');
        $this->assertFalse($kernel->isBootstrapped());

        $kernel->boost();
        $this->assertTrue($kernel->isBootstrapped());
    }

    public function test_kernel_handles_requests(): void
    {
        $kernel = new ApplicationKernel('testing');
        $kernel->boost();

        $response = $kernel->handle();

        $this->assertInstanceOf(Response::class, $response);
        // Now returns 404 since no routes are registered by default
        $this->assertEquals(404, $response->getStatus());
    }

    public function test_kernel_provides_timing_metrics(): void
    {
        $kernel = new ApplicationKernel('testing');
        $kernel->boost();

        $this->assertIsFloat($kernel->getBootTimeMs());
        $this->assertGreaterThanOrEqual(0, $kernel->getBootTimeMs());
    }

    public function test_kernel_environment(): void
    {
        $kernel = new ApplicationKernel('staging');
        $this->assertEquals('staging', $kernel->getEnvironment());
    }
}

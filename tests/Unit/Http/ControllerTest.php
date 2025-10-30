<?php

declare(strict_types=1);

namespace Hyperdrive\Tests\Unit\Http;

use Hyperdrive\Http\Controller;
use Hyperdrive\Http\Request;
use Hyperdrive\Http\Response;
use PHPUnit\Framework\TestCase;

class ControllerTest extends TestCase
{
    public function test_controller_can_be_instantiated(): void
    {
        $controller = new class() extends Controller {};

        $this->assertInstanceOf(Controller::class, $controller);
    }

    public function test_controller_can_return_json_response(): void
    {
        $controller = new class() extends Controller {
            public function testMethod(): Response
            {
                return $this->json(['message' => 'Hello World']);
            }
        };

        $response = $controller->testMethod();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatus());
        $this->assertEquals(['message' => 'Hello World'], $response->getData());
    }

    public function test_controller_can_return_json_with_custom_status(): void
    {
        $controller = new class() extends Controller {
            public function testMethod(): Response
            {
                return $this->json(['error' => 'Not Found'], 404);
            }
        };

        $response = $controller->testMethod();

        $this->assertEquals(404, $response->getStatus());
        $this->assertEquals(['error' => 'Not Found'], $response->getData());
    }
}

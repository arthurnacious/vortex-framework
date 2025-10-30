<?php

declare(strict_types=1);

namespace Hyperdrive\Tests\Unit\Http;

use Hyperdrive\Http\Request;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    public function test_request_can_be_created_from_globals(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/test';
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_POST = ['name' => 'John'];

        $request = Request::createFromGlobals();

        $this->assertInstanceOf(Request::class, $request);
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('/test', $request->getPath());
    }

    public function test_request_can_get_method(): void
    {
        $request = new Request('GET', '/test');

        $this->assertEquals('GET', $request->getMethod());
    }

    public function test_request_can_get_path(): void
    {
        $request = new Request('GET', '/users/123');

        $this->assertEquals('/users/123', $request->getPath());
    }

    public function test_request_can_get_and_set_data(): void
    {
        $request = new Request('POST', '/test');
        $data = ['name' => 'John', 'email' => 'john@example.com'];

        $request->setData($data);

        $this->assertEquals($data, $request->getData());
        $this->assertEquals('John', $request->getData('name'));
        $this->assertNull($request->getData('nonexistent'));
    }

    public function test_request_can_get_query_parameters(): void
    {
        $request = new Request('GET', '/test');
        $request->setQuery(['page' => '1', 'limit' => '10']);

        $this->assertEquals('1', $request->getQuery('page'));
        $this->assertEquals('10', $request->getQuery('limit'));
        $this->assertNull($request->getQuery('nonexistent'));
    }

    public function test_request_can_get_headers(): void
    {
        $request = new Request('GET', '/test', ['Content-Type' => 'application/json']);

        $this->assertEquals('application/json', $request->getHeader('Content-Type'));
        $this->assertEquals('application/json', $request->getHeader('content-type')); // case-insensitive
        $this->assertNull($request->getHeader('Nonexistent'));
    }

    public function test_request_can_extract_headers_from_server(): void
    {
        $_SERVER['HTTP_USER_AGENT'] = 'TestAgent';
        $_SERVER['HTTP_ACCEPT'] = 'application/json';
        $_SERVER['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';

        $request = Request::createFromGlobals();

        $this->assertEquals('TestAgent', $request->getHeader('User-Agent'));
        $this->assertEquals('application/json', $request->getHeader('Accept'));
        $this->assertEquals('application/x-www-form-urlencoded', $request->getHeader('Content-Type'));
    }

    public function test_request_can_handle_json_data(): void
    {
        // Instead of trying to write to php://input, we'll test the JSON parsing logic directly
        $jsonData = ['name' => 'John', 'email' => 'john@example.com'];
        $jsonString = json_encode($jsonData);

        // Create a mock stream for JSON data
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $jsonString);
        rewind($stream);

        // Test the private method via reflection
        $request = new Request();
        $reflection = new \ReflectionClass($request);
        $method = $reflection->getMethod('extractJsonData');
        $method->setAccessible(true);

        $result = $method->invokeArgs($request, [$stream, 'application/json']);

        fclose($stream);

        $this->assertEquals($jsonData, $result);
    }

    public function test_request_handles_invalid_json_gracefully(): void
    {
        $invalidJson = '{"name": "John", "email": }';
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $invalidJson);
        rewind($stream);

        $request = new Request();
        $reflection = new \ReflectionClass($request);
        $method = $reflection->getMethod('extractJsonData');
        $method->setAccessible(true);

        $result = $method->invokeArgs($request, [$stream, 'application/json']);

        fclose($stream);

        $this->assertEquals([], $result);
    }

    public function test_request_can_get_client_ip(): void
    {
        $_SERVER['REMOTE_ADDR'] = '192.168.1.100';
        $request = Request::createFromGlobals();

        $this->assertEquals('192.168.1.100', $request->getClientIp());
    }

    public function test_request_can_get_user_agent(): void
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 Test Browser';
        $request = Request::createFromGlobals();

        $this->assertEquals('Mozilla/5.0 Test Browser', $request->getUserAgent());
    }

    public function test_request_can_set_and_get_attributes(): void
    {
        $request = new Request();

        $request->setAttribute('user', ['id' => 123, 'name' => 'John']);
        $request->setAttribute('auth', true);

        $this->assertEquals(['id' => 123, 'name' => 'John'], $request->getAttribute('user'));
        $this->assertTrue($request->getAttribute('auth'));
        $this->assertNull($request->getAttribute('nonexistent'));
        $this->assertEquals(['user' => ['id' => 123, 'name' => 'John'], 'auth' => true], $request->getAttributes());
    }
}

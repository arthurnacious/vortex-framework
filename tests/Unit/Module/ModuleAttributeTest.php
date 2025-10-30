<?php

declare(strict_types=1);

namespace Hyperdrive\Tests\Unit\Module;

use Hyperdrive\Attributes\Module;
use PHPUnit\Framework\TestCase;

class ModuleAttributeTest extends TestCase
{
    public function test_module_attribute_can_be_created(): void
    {
        $module = new Module(
            name: 'User',
            version: '1.0.0',
            imports: ['Auth'],
            providers: ['UserService'],
            listeners: ['UserCreatedEvent' => ['SendWelcomeEmailListener']],
            routes: ['api' => true, 'web' => false]
        );

        $this->assertInstanceOf(Module::class, $module);
        $this->assertEquals('User', $module->name);
        $this->assertEquals('1.0.0', $module->version);
        $this->assertEquals(['Auth'], $module->imports);
        $this->assertEquals(['UserService'], $module->providers);
        $this->assertEquals(['UserCreatedEvent' => ['SendWelcomeEmailListener']], $module->listeners);
        $this->assertEquals(['api' => true, 'web' => false], $module->routes);
    }

    public function test_module_attribute_has_correct_target(): void
    {
        $reflection = new \ReflectionClass(Module::class);
        $attribute = $reflection->getAttributes()[0];

        $this->assertEquals(\Attribute::TARGET_CLASS, $attribute->getArguments()[0]);
    }

    public function test_module_attribute_with_defaults(): void
    {
        $module = new Module();

        $this->assertEquals('', $module->name);
        $this->assertEquals('', $module->version);
        $this->assertEquals([], $module->imports);
        $this->assertEquals([], $module->providers);
        $this->assertEquals([], $module->listeners);
        $this->assertEquals([], $module->routes);
    }
}

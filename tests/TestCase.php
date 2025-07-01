<?php

namespace V8\Tests;

use V8\Application;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected Application $app;

    protected function setUp(): void
    {
        $this->app = new Application(dirname(__DIR__));
        $this->app->getContainer()->singleton(
            \V8\Environment::class,
            fn() => new \V8\Environment()
        );
    }

    protected function get(string $class)
    {
        return $this->app->getContainer()->get($class);
    }
}

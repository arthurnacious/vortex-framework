<?php

namespace V8\Modules\Hello;

use V8\Core\Module;
use V8\Modules\Hello\Controllers\HelloController;
use V8\Modules\Hello\services\HelloService;

class HelloModule extends Module
{
    public function register(): void
    {
        $this->container->singleton(HelloService::class, fn() => new HelloService());

        $this->registerRoutes([HelloController::class]);
    }
}

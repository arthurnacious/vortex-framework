<?php

namespace App\Modules\Hello;

use App\Modules\Hello\Controllers\HelloController;
use App\Modules\Hello\Services\HelloService;
use V8\Module;

class HelloModule extends Module
{
    public function register(): void
    {
        $this->container->singleton(HelloService::class, fn() => new HelloService());

        $this->registerRoutes([HelloController::class]);
    }
}

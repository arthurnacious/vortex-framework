<?php

namespace V8\Modules\Hello;

use V8\Core\Module;
use V8\Modules\Hello\Controllers\HelloController;

class HelloModule extends Module
{
    public function register(): void
    {
        $this->registerRoutes([
            HelloController::class
        ]);
    }
}

<?php

namespace V8\Modules\Home;

use V8\Core\Module;
use V8\Modules\Home\Controllers\HomeController;

class HomeModule extends Module
{
    public function register(): void
    {
        $this->registerRoutes([HomeController::class]);
    }
}

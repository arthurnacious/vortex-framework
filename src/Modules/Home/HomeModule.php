<?php

namespace App\Modules\Home;

use V8\Module;
use App\Modules\Home\Controllers\HomeController;

class HomeModule extends Module
{
    public function register(): void
    {
        $this->registerRoutes([HomeController::class]);
    }
}

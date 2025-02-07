<?php

namespace App\Users;

use App\Middleware\AuthMiddleware;
use Framework\Module\BaseModule;
use App\Users\Controllers\UserController;
use App\Users\Services\UserService;

class UsersModule extends BaseModule
{
    public function getControllers(): array
    {
        return [
            UserController::class
        ];
    }

    public function getServices(): array
    {
        return [
            UserService::class
        ];
    }

    public function getMiddleware(): array
    {
        return [
            AuthMiddleware::class
        ];
    }
}
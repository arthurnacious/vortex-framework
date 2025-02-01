<?php

namespace App\Users;

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
            'auth' => Middleware\AuthMiddleware::class
        ];
    }
}
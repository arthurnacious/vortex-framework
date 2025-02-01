<?php

namespace App\Users\Controllers;

use Framework\Attributes\Path;
use Framework\Attributes\Get;
use Framework\Attributes\Post;
use App\Users\Services\UserService;
use App\Users\DTO\CreateUserDTO;

#[Path('/users')]
class UserController
{
    public function __construct(
        private UserService $userService
    ) {}

    #[Get('/')]
    public function index()
    {
        return $this->userService->getAllUsers();
    }

    #[Post('/')]
    public function create(CreateUserDTO $dto)
    {
        return $this->userService->createUser($dto);
    }
}
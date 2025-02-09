<?php
namespace App\Users\Controllers;

use Framework\Attributes\Path;
use Framework\Attributes\Get;
use Framework\Attributes\Post;
use Framework\Attributes\Middleware;
use App\Middleware\AuthMiddleware;
use App\Users\Services\UserService;
use App\Users\DTO\CreateUserDTO;
use Framework\Http\Request;
use Framework\Http\Response;

#[Path('/users')]
#[Middleware(AuthMiddleware::class)]
class UserController
{
    public function __construct(private UserService $userService) {}

    #[Get('/')]
    public function index(): void
    {
        Response::json($this->userService->getAllUsers());
    }

    #[Get('/{userid}')]
    public function show(string $userid): void
    {
        $user = $this->userService->getUserById($userid);
        if (!$user) {
            Response::json(['error' => 'User not found'], 404);
            return;      
        }

        Response::json([
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'password' => $user['password']
        ]);
    }

    #[Post('/')]
    #[Middleware(AuthMiddleware::class)]
    public function create(Request $request): void
    {
        $dto = new CreateUserDTO(name: $request->getPostData()['name'], email: $request->getPostData()['email'], password: $request->getPostData()['password']);
        $user = $this->userService->createUser($dto);
        Response::json([
            'id' => $user[0],
            'name' => $user[1],
            'email' => $user[2],            
            'password' => $user[3]
        ], 201);
    }
}

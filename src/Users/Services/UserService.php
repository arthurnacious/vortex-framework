<?php

namespace App\Users\Services;

use App\Users\Models\User;
use App\Users\DTO\CreateUserDTO;

class UserService
{
    private array $users = [
        ['id' => 1, 'name' =>'John Doe', 'email' => 'john@example.com', 'password' => 'password'],
        ['id' => 2, 'name' =>'Jane Doe', 'email' => 'jane@example.com', 'password' => 'password'],
        ['id' => 3, 'name' =>'Bob Smith', 'email' => 'bob@example.com', 'password' => 'password'],
    ];

    public function getAllUsers(): array
    {
        return $this->users;
    }

    public function createUser(CreateUserDTO $dto): User
    {
        array_push($this->users, $dto);
        return $$this->users;
    }

    public function getUserById(int $id): ?array
    {
        $user = array_filter($this->users, fn($user) => $user['id'] === $id);
        $user = array_values($user);
        
        return $user[0] ?? null;
    }
}

<?php

namespace App\Users\Services;

use App\Users\Models\User;
use App\Users\DTO\CreateUserDTO;

class UserService
{
    private array $users = [
        [1, 'John Doe', 'john@example.com', 'password'],
        [2, 'Jane Doe', 'jane@example.com', 'password'],
        [3, 'Bob Smith', 'bob@example.com', 'password'],
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
}

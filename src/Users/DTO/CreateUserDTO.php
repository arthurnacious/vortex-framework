<?php

namespace App\Users\DTO;

class CreateUserDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password
    ) {}
}

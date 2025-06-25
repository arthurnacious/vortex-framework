<?php

namespace V8\Modules\Hello\services;


class HelloService
{
    public function hello(): string
    {
        return 'Hello from V8!';
    }

    public function great(string $name, string $surname, string $birthDate, ?string $isHungry = null): array
    {
        return [
            'name' => $name,
            'surname' => $surname,
            'birthdate' => $birthDate,
            'isHungry' => $isHungry
        ];
    }
}

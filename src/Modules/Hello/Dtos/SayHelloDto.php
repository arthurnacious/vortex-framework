<?php

namespace V8\Modules\Hello\Dtos;

use V8\Core\DataTransferObject;

class SayHelloDto extends DataTransferObject
{
    public string $name;
    public string $surname;
    public string $birthDate;
    public ?string $isHungry = 'yes'; // Optional field

    protected function rules(): array
    {
        return [
            'name' => 'required',
            'surname' => 'required',
            'birthDate' => 'required|date',
            'isHungry' => 'in:yes,no'
        ];
    }
}

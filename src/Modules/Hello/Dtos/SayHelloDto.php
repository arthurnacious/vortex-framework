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

    protected function fieldNames(): array
    {
        return [
            'name' => 'Full Name',
            'surname' => 'Last Name',
            'birthDate' => 'Birth Date',
            'isHungry' => 'Is Hungry'
        ];
    }

    protected function messages(): array
    {
        return [
            'name' => [
                'required' => 'Full name is mandatory'
            ],
            'surname' => [
                'required' => 'Last name is mandatory'
            ],
            'birthDate' => [
                'required' => 'Birth date is mandatory',
                'date' => 'Birth date must be a hase date ndoda',
            ],
            'isHungry' => [
                'in' => 'Is hungry must be yes or no'
            ]
        ];
    }
}

<?php

declare(strict_types=1);

namespace Hyperdrive\Tests\Unit\Dto;

use Hyperdrive\Dto\DataTransferObject;
use Hyperdrive\Http\Request;
use PHPUnit\Framework\TestCase;

class UserRegistrationDto extends DataTransferObject
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public int $age,
        public bool $newsletter = false,
        public array $interests = []
    ) {}

    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string',
            'age' => 'required|int',
            'newsletter' => 'bool',
            'interests' => 'array'
        ];
    }
}

class FlexibleDto extends DataTransferObject
{
    public function __construct(
        public ?string $optionalString = null,
        public int $optionalInt = 0,
        public array $optionalArray = [],
        public bool $optionalBool = false
    ) {}

    public function rules(): array
    {
        return [
            'optionalString' => 'string',
            'optionalInt' => 'int',
            'optionalArray' => 'array',
            'optionalBool' => 'bool'
        ];
    }
}

class DataTransferObjectValidationTest extends TestCase
{
    public function test_validation_passes_with_valid_data(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secret123',
            'age' => 25,
            'newsletter' => true,
            'interests' => ['php', 'laravel']
        ];

        $dto = UserRegistrationDto::fromArray($data);

        $this->assertTrue($dto->validate());
        $this->assertEmpty($dto->getErrors());
    }

    public function test_validation_fails_with_missing_required_fields(): void
    {
        $data = [
            'name' => 'John Doe',
        ];

        $dto = UserRegistrationDto::fromArray($data);

        $this->assertFalse($dto->validate());
        $this->assertNotEmpty($dto->getErrors());
    }

    public function test_validation_fails_with_invalid_email(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'password' => 'secret123',
            'age' => 25
        ];

        $dto = UserRegistrationDto::fromArray($data);

        $this->assertFalse($dto->validate());
        $this->assertArrayHasKey('email', $dto->getErrors());
    }

    public function test_dto_handles_default_values_correctly(): void
    {
        $data = [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'secret123',
            'age' => 30
        ];

        $dto = UserRegistrationDto::fromArray($data);

        $this->assertFalse($dto->newsletter);
        $this->assertEquals([], $dto->interests);
        $this->assertTrue($dto->validate());
    }
}

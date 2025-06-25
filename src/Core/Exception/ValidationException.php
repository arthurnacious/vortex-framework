<?php

namespace V8\Core\Exception;

use Exception;

class ValidationException extends Exception
{
    protected array $errors;

    public function __construct(array $errors, string $message = 'Validation failed')
    {
        $this->errors = $errors;

        // Set the HTTP status code to 422 immediately
        http_response_code(422);

        // Output the response and exit
        echo json_encode([
            'message' => $message,
            'errors' => $errors,
        ]);

        exit;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}

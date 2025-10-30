<?php

declare(strict_types=1);

namespace Hyperdrive\Http;

class Response
{
    public function __construct(
        private mixed $data = null,
        private int $status = 200,
        private array $headers = []
    ) {
        // Set default content type if not provided
        if (!isset($this->headers['Content-Type'])) {
            $this->headers['Content-Type'] = 'application/json; charset=utf-8';
        }
    }

    public function send(): void
    {
        // Set HTTP status
        http_response_code($this->status);

        // Set headers
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }

        // Send JSON response
        echo json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public static function json(mixed $data, int $status = 200): self
    {
        return new self($data, $status);
    }

    public function withHeader(string $name, string $value): self
    {
        $new = clone $this;
        $new->headers[$name] = $value;
        return $new;
    }

    public function withStatus(int $status): self
    {
        $new = clone $this;
        $new->status = $status;
        return $new;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getData(): mixed
    {
        return $this->data;
    }
}

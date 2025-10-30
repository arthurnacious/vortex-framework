<?php

declare(strict_types=1);

namespace Hyperdrive\Http;

class Request
{
    private string $method;
    private string $path;
    private array $headers;
    private array $data;
    private array $query;
    private array $attributes;

    public function __construct(
        string $method = 'GET',
        string $path = '/',
        array $headers = [],
        array $data = [],
        array $query = []
    ) {
        $this->method = strtoupper($method);
        $this->path = $path;
        $this->headers = $headers;
        $this->data = $data;
        $this->query = $query;
        $this->attributes = [];
    }

    public static function createFromGlobals(): self
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';

        $headers = self::extractHeaders();
        $data = self::extractData($method);
        $query = $_GET;

        return new self($method, $path, $headers, $data, $query);
    }

    private static function extractHeaders(): array
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $headerName = str_replace('_', '-', ucwords(strtolower(substr($key, 5)), '-'));
                $headers[$headerName] = $value;
            } elseif (in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH', 'CONTENT_MD5'], true)) {
                $headerName = str_replace('_', '-', ucwords(strtolower($key), '-'));
                $headers[$headerName] = $value;
            }
        }
        return $headers;
    }

    private static function extractData(string $method): array
    {
        $data = [];

        if ($method === 'POST') {
            $data = $_POST;
        } elseif (in_array($method, ['PUT', 'PATCH', 'DELETE'])) {
            parse_str(file_get_contents('php://input'), $data);
        }

        // Also handle JSON input
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            $jsonData = self::extractJsonData(fopen('php://input', 'r'), $contentType);
            $data = array_merge($data, $jsonData);
        }

        return $data;
    }

    private static function extractJsonData($stream, string $contentType): array
    {
        if (!str_contains($contentType, 'application/json')) {
            return [];
        }

        $jsonString = stream_get_contents($stream);
        if ($jsonString === false || $jsonString === '') {
            return [];
        }

        $data = json_decode($jsonString, true);

        return is_array($data) ? $data : [];
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getHeader(string $name): ?string
    {
        // Try exact match first
        if (isset($this->headers[$name])) {
            return $this->headers[$name];
        }

        // Try case-insensitive match
        $normalizedName = strtolower($name);
        foreach ($this->headers as $headerName => $value) {
            if (strtolower($headerName) === $normalizedName) {
                return $value;
            }
        }

        return null;
    }

    public function getData(?string $key = null): mixed
    {
        if ($key === null) {
            return $this->data;
        }

        return $this->data[$key] ?? null;
    }

    public function setData(array $data): void
    {
        $this->data = $data;
    }

    public function getQuery(?string $key = null): mixed
    {
        if ($key === null) {
            return $this->query;
        }

        return $this->query[$key] ?? null;
    }

    public function setQuery(array $query): void
    {
        $this->query = $query;
    }

    public function setAttribute(string $name, mixed $value): void
    {
        $this->attributes[$name] = $value;
    }

    public function getAttribute(string $name): mixed
    {
        return $this->attributes[$name] ?? null;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getClientIp(): string
    {
        return $_SERVER['HTTP_CLIENT_IP']
            ?? $_SERVER['HTTP_X_FORWARDED_FOR']
            ?? $_SERVER['REMOTE_ADDR']
            ?? '127.0.0.1';
    }

    public function getUserAgent(): ?string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? null;
    }
}

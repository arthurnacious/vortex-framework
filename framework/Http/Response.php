<?php

namespace Framework\Http;

class Response
{
    private int $statusCode = 200;
    private array $headers = [];
    private mixed $content;

    public function setContent(mixed $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function setStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    public function addHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function send(): void
    {
        // Set status code
        http_response_code($this->statusCode);

        // Set headers
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }

        // Set content
        if (is_array($this->content) || is_object($this->content)) {
            header('Content-Type: application/json');
            echo json_encode($this->content);
        } else {
            echo $this->content;
        }
    }
}
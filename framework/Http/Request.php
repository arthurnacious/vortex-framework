<?php
namespace Framework\Http;

class Request
{
    private array $queryParams;
    private array $postData;
    private array $serverData;
    private array $cookies;
    private array $files;

    public function __construct()
    {
        $this->queryParams = $_GET;
        $this->postData = $_POST;
        $this->serverData = $_SERVER;
        $this->cookies = $_COOKIE;
        $this->files = $_FILES;
    }

    public function getMethod(): string
    {
        return ($this->serverData['REQUEST_METHOD'] !== 'get' && isset($_POST['_method'])) ? $_POST['_method'] : $this->serverData['REQUEST_METHOD'];
    }

    public function getUri(): string
    {
        return parse_url($this->serverData['REQUEST_URI'], PHP_URL_PATH);
    }

    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    public function getPostData(): array
    {
        return $this->postData;
    }

    public function getJson(): ?array
    {
        $content = file_get_contents('php://input');
        return json_decode($content, true);
    }
}
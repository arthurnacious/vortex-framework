<?php

namespace V8\Modules\Hello\Controllers;

use V8\Core\Attributes\Route;
use V8\Core\Attributes\HttpMethod;
use Symfony\Component\HttpFoundation\Response;

#[Route('/hello')]
class HelloController
{
    #[HttpMethod('/')]
    public function index(): Response
    {
        return new Response('Hello from V8!');
    }

    #[HttpMethod('/{name}', method: HttpMethod::GET)]
    public function greet(string $name): Response
    {
        return new Response("Hello, $name!");
    }

    #[HttpMethod('/create', method: HttpMethod::POST)]
    public function create(): Response
    {
        return new Response('Item created!', 201);
    }

    #[HttpMethod('/{id}', method: HttpMethod::PUT)]
    public function update(string $id): Response
    {
        return new Response("Item $id updated!");
    }

    #[HttpMethod('/{id}', method: HttpMethod::DELETE)]
    public function delete(string $id): Response
    {
        return new Response("Item $id deleted!", 204);
    }
}

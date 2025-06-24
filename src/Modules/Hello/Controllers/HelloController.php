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
}

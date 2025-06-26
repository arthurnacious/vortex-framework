<?php

namespace V8\Tests\Stubs;

use V8\Core\Attributes\Route;
use Symfony\Component\HttpFoundation\Response;
use V8\Core\Attributes\Path;

#[Route('/test')]
class TestController
{
    #[Path('/', method: 'GET')]
    public function index(): Response
    {
        return new Response('Test response');
    }
}

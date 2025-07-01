<?php

namespace App\Modules\Hello\Controllers;

use App\Modules\Hello\Dtos\SayHelloDto;
use App\Modules\Hello\Services\HelloService;
use Symfony\Component\HttpFoundation\Request;
use V8\Attributes\Route;
use V8\Attributes\HttpMethod;
use Symfony\Component\HttpFoundation\Response;
use V8\Attributes\Middleware;
use V8\Attributes\Path;
use V8\Controller\BaseController;

#[Route('/hello')]
class HelloController extends BaseController
{
    public function __construct(private HelloService $helloService) {}

    #[Path('/')]
    public function index(Request $request): String
    {
        return $this->helloService->hello($request);
    }

    #[Path('/{name}/{surname}', method: 'GET')]
    public function greet(string $name, string $surname): Response
    {
        return new Response("Hello, $name $surname!");
    }

    #[Path('/', method: 'POST')]
    #[Middleware(\App\Middleware\Authenticate::class)]
    public function create(SayHelloDto $dto): Response
    {
        $data = $this->helloService->great(
            $dto->name,
            $dto->surname,
            $dto->birthDate,
            $dto->isHungry
        );

        return $this->created($data);
    }

    #[Path('/{id}', method: HttpMethod::PUT)]
    public function update(string $id): Response
    {
        return new Response("Item $id updated!");
    }

    #[Path('/{id}', method: HttpMethod::DELETE)]
    public function delete(string $id): Response
    {
        return new Response("Item $id deleted!", 204);
    }
}

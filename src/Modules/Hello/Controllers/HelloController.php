<?php

namespace V8\Modules\Hello\Controllers;

use Symfony\Component\HttpFoundation\Request;
use V8\Core\Attributes\Route;
use V8\Core\Attributes\HttpMethod;
use Symfony\Component\HttpFoundation\Response;
use V8\Core\Attributes\Middleware;
use V8\Core\Attributes\Path;
use V8\Core\Controller\BaseController;
use V8\Modules\Hello\Dtos\SayHelloDto;
use V8\Modules\Hello\Services\HelloService;

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

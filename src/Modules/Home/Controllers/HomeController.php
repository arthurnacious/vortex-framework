<?php

namespace V8\Modules\Home\Controllers;

use Symfony\Component\HttpFoundation\Response;
use V8\Core\Attributes\Route;
use V8\Core\Attributes\Path;
use V8\Core\Controller\BaseController;

#[Route('/')]
class HomeController extends BaseController
{
    #[Path('/', method: 'GET')]
    public function index(): Response
    {
        return new Response('Welcome to Vortex-8!');
    }
}

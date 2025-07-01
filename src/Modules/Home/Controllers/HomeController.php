<?php

namespace App\Modules\Home\Controllers;

use Symfony\Component\HttpFoundation\Response;
use V8\Attributes\Route;
use V8\Attributes\Path;
use V8\Controller\BaseController;

#[Route('/')]
class HomeController extends BaseController
{
    #[Path('/', method: 'GET')]
    public function index(): Response
    {
        return new Response('Welcome to Vortex-8!');
    }
}

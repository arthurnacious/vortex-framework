<?php

namespace App\Middleware;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use V8\Core\Contracts\MiddlewareInterface;

class Authenticate implements MiddlewareInterface
{
    public function handle(
        Request $request,
        callable $next,
        array $params = []  // Added default value
    ): Response {
        if (!$request->headers->has('X-Auth-Token')) {
            return new Response('Unauthorized', 401);
        }

        return $next($request);
    }
}

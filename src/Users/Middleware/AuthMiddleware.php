<?php

namespace App\Users\Middleware;

use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Middleware\MiddlewareInterface;

class AuthMiddleware implements MiddlewareInterface
{
    public function process(Request $request, callable $next): Response
    {
        // Authentication logic
        return $next($request);
    }
}
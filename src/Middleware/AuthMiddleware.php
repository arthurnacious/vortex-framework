<?php

namespace App\Middleware;

use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Middleware\MiddlewareInterface;

class AuthMiddleware implements MiddlewareInterface
{
    public function process(Request $request, callable $next): Response
    {
        if (!$this->isAuthenticated()) {
            // Return unauthorized response or redirect
            return new Response('Unauthorized', 401);
        }
        
        return $next($request);
    }

    private function isAuthenticated(): bool {
        // Authentication logic
        return false;
    }
}
<?php
// framework/Middleware/MiddlewareInterface.php
namespace Framework\Middleware;

use Framework\Http\Request;
use Framework\Http\Response;

interface MiddlewareInterface
{
    /**
     * Process the request through middleware
     * 
     * @param Request $request Incoming request
     * @param callable $next Next middleware or final handler
     * @return Response
     */
    public function process(Request $request, callable $next): Response;
}
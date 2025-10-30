<?php

declare(strict_types=1);

namespace Hyperdrive\Contracts\Middleware;

use Hyperdrive\Http\Request;
use Hyperdrive\Http\Response;

interface MiddlewareInterface
{
    public function handle(Request $request, \Closure $next): Response;
}

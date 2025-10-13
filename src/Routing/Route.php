<?php

declare(strict_types=1);

namespace Hyperdrive\Routing;

class Route
{
    public function __construct(
        public string $method,
        public string $uri,
        public string $controller,
        public string $action,
        public string $name = '',
        public array $middleware = []
    ) {
    }
}
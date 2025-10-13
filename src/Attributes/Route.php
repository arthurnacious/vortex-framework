<?php

declare(strict_types=1);

namespace Hyperdrive\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Route
{
    public function __construct(
        public string $prefix = '',
        public array $middleware = []
    ) {
    }
}
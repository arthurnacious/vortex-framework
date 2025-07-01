<?php

namespace V8\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Middleware
{
    public function __construct(
        public string $middleware,
        public array $parameters = []
    ) {}
}

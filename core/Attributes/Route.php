<?php

namespace V8\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Route
{
    public function __construct(
        public string $path,
        public ?string $name = null
    ) {}
}

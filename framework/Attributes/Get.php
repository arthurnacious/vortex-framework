<?php

namespace Framework\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Get {
    public function __construct(public string $path = '/') {}
}
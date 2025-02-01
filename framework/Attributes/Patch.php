<?php

namespace Framework\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Patch {
    public function __construct(public string $path = '/') {}
}
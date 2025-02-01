<?php

namespace Framework\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Delete {
    public function __construct(public string $path = '/') {}
}
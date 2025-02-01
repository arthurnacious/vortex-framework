<?php

namespace Framework\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Post {
    public function __construct(public string $path = '/') {}
}
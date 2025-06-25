<?php

namespace V8\Core\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Path
{
    public function __construct(
        public string $path,
        public string $method = HttpMethod::GET
    ) {
        $this->method = strtoupper($method);
    }
}

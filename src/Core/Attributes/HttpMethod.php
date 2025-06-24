<?php

namespace V8\Core\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class HttpMethod
{
    public const GET = 'GET';
    public const POST = 'POST';
    public const PUT = 'PUT';
    public const DELETE = 'DELETE';
    public const PATCH = 'PATCH';
    public const OPTIONS = 'OPTIONS';
    public const HEAD = 'HEAD';

    public function __construct(
        public string $path,
        public string $method = self::GET,
        public ?string $name = null
    ) {
        $this->method = strtoupper($method);
    }
}

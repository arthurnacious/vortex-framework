<?php

namespace V8\Attributes;

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
}

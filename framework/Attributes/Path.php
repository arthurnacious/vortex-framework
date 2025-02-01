<?php

namespace Framework\Attributes;


class Path
{
    public function __construct(public string $path = '/') {}
}
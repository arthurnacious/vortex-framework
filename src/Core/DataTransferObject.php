<?php

namespace V8\Core;

use Symfony\Component\HttpFoundation\Request;

abstract class DataTransferObject
{
    public function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    public static function fromRequest(Request $request): static
    {
        return new static($request->toArray());
    }
}

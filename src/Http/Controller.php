<?php

declare(strict_types=1);

namespace Hyperdrive\Http;

class Controller
{
    protected function json(mixed $data, int $status = 200): Response
    {
        return Response::json($data, $status);
    }
}

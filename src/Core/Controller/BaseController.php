<?php

namespace V8\Core\Controller;

use Symfony\Component\HttpFoundation\Response;

abstract class BaseController
{
    // Successful responses
    protected function ok($data = null, array $headers = []): Response
    {
        return $this->response($data, Response::HTTP_OK, $headers);
    }

    protected function created($data = null, array $headers = []): Response
    {
        return $this->response($data, Response::HTTP_CREATED, $headers);
    }

    protected function updated($data = null, array $headers = []): Response
    {
        return $this->response($data, Response::HTTP_OK, $headers);
    }

    protected function deleted($data = null, array $headers = []): Response
    {
        return $this->response($data, Response::HTTP_NO_CONTENT, $headers);
    }

    // Error responses
    protected function notFound(string $message = 'Not Found'): Response
    {
        return $this->response(['error' => $message], Response::HTTP_NOT_FOUND);
    }

    // Universal formatter
    private function response($data, int $status, array $headers = []): Response
    {
        $headers['Content-Type'] ??= 'application/json';

        return new Response(
            $data === null ? null : json_encode($data),
            $status,
            $headers
        );
    }
}

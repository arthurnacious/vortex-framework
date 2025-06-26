<?php

namespace V8\Core;

class Environment
{
    protected array $config;

    public function __construct()
    {
        $this->config = require __DIR__ . '/../../config/environment/project.php';
    }

    public function get(string $key, $default = null)
    {
        // Check .env first
        if ($value = $_ENV[strtoupper($key)] ?? null) {
            return $value;
        }

        // Check config array
        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }
}

<?php

declare(strict_types=1);

namespace Hyperdrive\Support;

class Env
{
    private static array $cache = [];
    private static bool $loaded = false;

    public static function get(string $key, mixed $default = null): mixed
    {
        if (!self::$loaded) {
            self::load();
        }

        return self::$cache[$key] ?? $default;
    }

    public static function load(?string $path = null): void
    {
        if (self::$loaded) {
            return;
        }

        $path = $path ?? dirname(__DIR__, 2) . '/.env';

        if (!file_exists($path)) {
            self::$loaded = true;
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            [$name, $value] = self::parseLine($line);
            if ($name !== null) {
                self::$cache[$name] = $value;
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }

        self::$loaded = true;
    }

    private static function parseLine(string $line): array
    {
        if (strpos($line, '=') === false) {
            return [null, null];
        }

        [$name, $value] = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);

        // Remove quotes if present
        if (preg_match('/^([\'"])(.*)\1$/', $value, $matches)) {
            $value = $matches[2];
        }

        return [$name, $value];
    }

    public static function getEngine(): string
    {
        return self::get('HYPERDRIVE_ENGINE', 'auto');
    }

    public static function getHost(): string
    {
        return self::get('HYPERDRIVE_HOST', '0.0.0.0');
    }

    public static function getPort(): int
    {
        return (int) self::get('HYPERDRIVE_PORT', '8000');
    }

    public static function isDebug(): bool
    {
        return self::get('HYPERDRIVE_DEBUG', 'false') === 'true';
    }
}

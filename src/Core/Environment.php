<?php

namespace V8\Core;

use Symfony\Component\Dotenv\Dotenv;
use RuntimeException;

class Environment
{
    private static array $config = [];

    public static function load(string $rootPath): void
    {
        if (empty(self::$config)) {
            // Load all environment config files
            self::$config = self::loadConfigFiles($rootPath . '/config/environment');

            // Load .env if exists
            $dotenv = new Dotenv();
            $envPath = $rootPath . '/.env';

            if (file_exists($envPath)) {
                $dotenv->load($envPath);
            }

            self::mergeEnvWithConfig();
        }
    }

    private static function loadConfigFiles(string $configDir): array
    {
        if (!is_dir($configDir)) {
            throw new RuntimeException("Config directory not found: {$configDir}");
        }

        $config = [];
        $files = glob($configDir . '/*.php');

        foreach ($files as $file) {
            $fileConfig = require $file;

            if (!is_array($fileConfig)) {
                throw new RuntimeException("Config file must return an array: {$file}");
            }

            $config = array_merge_recursive($config, $fileConfig);
        }

        return $config;
    }

    public static function get(string $key, $default = null)
    {
        self::ensureInitialized();

        $value = $_ENV[strtoupper(str_replace('.', '_', $key))]
            ?? self::getFromConfig($key);

        return $value ?? $default;
    }

    private static function ensureInitialized(): void
    {
        if (empty(self::$config)) {
            throw new RuntimeException('Environment not loaded. Call Environment::load() first');
        }
    }

    private static function getFromConfig(string $key)
    {
        $keys = explode('.', $key);
        $value = self::$config;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return null;
            }
            $value = $value[$k];
        }

        return $value;
    }

    private static function mergeEnvWithConfig(): void
    {
        $flattened = self::flattenConfig(self::$config);

        foreach ($flattened as $envKey => $value) {
            if (!isset($_ENV[$envKey])) {
                $_ENV[$envKey] = is_array($value) ? json_encode($value) : $value;
            }
        }
    }

    private static function flattenConfig(array $config, string $prefix = ''): array
    {
        $result = [];

        foreach ($config as $key => $value) {
            $fullKey = $prefix ? "{$prefix}_{$key}" : $key;
            $fullKeyUpper = strtoupper($fullKey);

            if (is_array($value)) {
                $result = array_merge($result, self::flattenConfig($value, $fullKeyUpper));
            } else {
                $result[$fullKeyUpper] = $value;
            }
        }

        return $result;
    }
}

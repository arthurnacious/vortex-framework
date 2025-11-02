<?php

declare(strict_types=1);

namespace Hyperdrive\Server;

use Hyperdrive\Support\Env;

class ServerFactory
{
    public static function create(): ServerInterface
    {
        $engine = self::detectEngine();

        return match ($engine) {
            'openswoole' => new OpenSwooleServer(),
            'swoole' => new SwooleServer(),
            'roadstar' => new RoadstarServer(),
            default => throw new \RuntimeException("Unsupported engine: {$engine}")
        };
    }

    public static function detectEngine(): string
    {
        $configuredEngine = Env::getEngine();

        // If explicitly configured, use that
        if ($configuredEngine !== 'auto') {
            return self::validateEngine($configuredEngine);
        }

        // Auto-detect based on available extensions (using your existing logic)
        if (extension_loaded('openswoole')) {
            return 'openswoole';
        }

        if (extension_loaded('swoole')) {
            return 'swoole';
        }

        // Fallback to traditional PHP (your existing "roadster" but let's use "roadstar" for consistency)
        return 'roadstar';
    }

    private static function validateEngine(string $engine): string
    {
        $availableEngines = ['openswoole', 'swoole', 'roadstar'];

        if (!in_array($engine, $availableEngines)) {
            throw new \InvalidArgumentException("Invalid engine: {$engine}. Available: " . implode(', ', $availableEngines));
        }

        // Check if the required extension is loaded
        if ($engine === 'openswoole' && !extension_loaded('openswoole')) {
            throw new \RuntimeException('OpenSwoole engine requested but openswoole extension not loaded');
        }

        if ($engine === 'swoole' && !extension_loaded('swoole')) {
            throw new \RuntimeException('Swoole engine requested but swoole extension not loaded');
        }

        return $engine;
    }

    public static function getServerInfo(): array
    {
        $engine = self::detectEngine();

        $info = [
            'engine' => $engine,
            'host' => Env::getHost(),
            'port' => Env::getPort(),
            'debug' => Env::isDebug(),
        ];

        // Add extension-specific info
        if ($engine === 'openswoole') {
            $info['openswoole_version'] = phpversion('openswoole');
        } elseif ($engine === 'swoole') {
            $info['swoole_version'] = phpversion('swoole');
        }

        return $info;
    }
}

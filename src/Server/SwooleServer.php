<?php

declare(strict_types=1);

namespace Hyperdrive\Server;

use Hyperdrive\Support\Env;

class SwooleServer implements ServerInterface
{
    public function start(): void
    {
        if (!extension_loaded('swoole')) {
            throw new \RuntimeException('Swoole extension not loaded');
        }

        $host = Env::getHost();
        $port = Env::getPort();

        echo "🚀 Hyperdrive Swoole Server starting...\n";
        echo "📍 Server: http://{$host}:{$port}\n";
        echo "⚡ Engine: Swoole " . phpversion('swoole') . "\n";
        echo "🔧 Mode: " . (Env::isDebug() ? 'Development' : 'Production') . "\n";
        echo "📋 Press Ctrl+C to stop the server\n\n";

        // For now, fall back to Roadstar until we implement full Swoole server
        echo "⚠️  Swoole server not fully implemented yet. Falling back to Roadstar...\n";
        (new RoadstarServer())->start();
    }
}

<?php

declare(strict_types=1);

namespace Hyperdrive\Server;

use Hyperdrive\Support\Env;

class RoadstarServer implements ServerInterface
{
    public function start(): void
    {
        $host = Env::getHost();
        $port = Env::getPort();

        echo "🚀 Hyperdrive Roadstar Server starting...\n";
        echo "📍 Server: http://{$host}:{$port}\n";
        echo "⚡ Engine: Traditional PHP\n";
        echo "🔧 Mode: " . (Env::isDebug() ? 'Development' : 'Production') . "\n";
        echo "📋 Press Ctrl+C to stop the server\n\n";

        passthru("php -S {$host}:{$port} public/index.php");
    }
}

<?php

declare(strict_types=1);

namespace Hyperdrive\Server;

use Hyperdrive\Support\Env;
use Hyperdrive\Hyperdrive;
use OpenSwoole\Constant;

class OpenSwooleServer implements ServerInterface
{
    private \OpenSwoole\Http\Server $server;
    private float $serverStartTime;

    public function start(): void
    {
        $this->serverStartTime = microtime(true);

        $host = Env::getHost();
        $port = Env::getPort();

        $this->server = new \OpenSwoole\Http\Server($host, $port, Constant::SERVER_PROCESS, Constant::SOCK_TCP);

        $this->configureServer();
        $this->setupHandlers();

        echo "🚀 Hyperdrive OpenSwoole Server starting...\n";
        echo "📍 Server: http://{$host}:{$port}\n";
        echo "⚡ Engine: OpenSwoole " . phpversion('openswoole') . "\n";
        echo "🔧 Mode: " . (Env::isDebug() ? 'Development' : 'Production') . "\n";
        echo "🔄 Workers: " . (swoole_cpu_num() * 2) . " (CPU: " . swoole_cpu_num() . ")\n";
        echo "📋 Press Ctrl+C to stop the server\n\n";

        $this->server->start();
    }

    private function configureServer(): void
    {
        $this->server->set([
            // Worker processes
            'worker_num' => swoole_cpu_num() * 2,
            'max_request' => 10000,
            'max_conn' => 100000,

            // Coroutine settings
            'enable_coroutine' => true,
            'max_coroutine' => 300000,

            // Logging
            'log_file' => dirname(__DIR__, 2) . '/storage/logs/openswoole.log',
            'log_level' => Env::isDebug() ? Constant::LOG_DEBUG : Constant::LOG_INFO,

            // Static file handling
            'enable_static_handler' => true,
            'document_root' => dirname(__DIR__, 2) . '/public',
            'static_handler_locations' => ['/css', '/js', '/images'],

            // Performance optimizations
            'reactor_num' => swoole_cpu_num() * 2,
            'task_worker_num' => swoole_cpu_num(),
            'task_enable_coroutine' => true,
            'buffer_output_size' => 32 * 1024 * 1024,

            // HTTP settings
            'http_compression' => true,
            'http_compression_level' => 6,
            'package_max_length' => 100 * 1024 * 1024,
        ]);
    }

    private function setupHandlers(): void
    {
        $this->server->on('start', function (\OpenSwoole\Http\Server $server) {
            $uptime = round(microtime(true) - $this->serverStartTime, 3);
            echo "✅ OpenSwoole Server started successfully ({$uptime}s)\n";
            echo "🔄 Worker processes: {$server->setting['worker_num']}\n";

            if (Env::isDebug()) {
                echo "🐛 Debug mode: ENABLED\n";
            }
        });

        $this->server->on('shutdown', function () {
            echo "🛑 OpenSwoole Server stopped\n";
        });

        $this->server->on('workerstart', function (\OpenSwoole\Http\Server $server, int $workerId) {
            if (Env::isDebug()) {
                echo "👷 Worker #{$workerId} started\n";
            }
        });

        $this->server->on('workerstop', function (\OpenSwoole\Http\Server $server, int $workerId) {
            if (Env::isDebug()) {
                echo "👷 Worker #{$workerId} stopped\n";
            }
        });

        $this->server->on('request', function (\OpenSwoole\Http\Request $openSwooleRequest, \OpenSwoole\Http\Response $openSwooleResponse) {
            // Convert OpenSwoole request to Hyperdrive request
            $hyperdriveRequest = $this->createRequestFromOpenSwoole($openSwooleRequest);

            // Handle through Hyperdrive kernel
            $hyperdriveResponse = $this->handleRequest($hyperdriveRequest);

            // Convert Hyperdrive response to OpenSwoole response
            $this->sendOpenSwooleResponse($openSwooleResponse, $hyperdriveResponse);
        });

        $this->server->on('task', function (\OpenSwoole\Http\Server $server, $taskId, $reactorId, $data) {
            // Generic task processing
            return $this->handleTask($data);
        });

        $this->server->on('finish', function (\OpenSwoole\Http\Server $server, $taskId, $data) {
            if (Env::isDebug()) {
                echo "✅ Task #{$taskId} completed\n";
            }
        });
    }

    private function createRequestFromOpenSwoole(\OpenSwoole\Http\Request $openSwooleRequest): \Hyperdrive\Http\Request
    {
        $method = $openSwooleRequest->server['request_method'] ?? 'GET';
        $uri = $openSwooleRequest->server['request_uri'] ?? '/';
        $headers = $openSwooleRequest->header ?? [];
        $get = $openSwooleRequest->get ?? [];
        $post = $openSwooleRequest->post ?? [];
        $files = $openSwooleRequest->files ?? [];
        $cookies = $openSwooleRequest->cookie ?? [];

        // Combine all data
        $data = array_merge($get, $post);

        $request = new \Hyperdrive\Http\Request($method, $uri, $headers, $data, $get);

        // Set additional data
        foreach ($files as $key => $file) {
            $request->setAttribute("file_{$key}", $file);
        }

        foreach ($cookies as $key => $value) {
            $request->setAttribute("cookie_{$key}", $value);
        }

        // Add OpenSwoole info
        $request->setAttribute('coroutine_id', \OpenSwoole\Coroutine::getCid());

        return $request;
    }

    private function handleRequest(\Hyperdrive\Http\Request $request): \Hyperdrive\Http\Response
    {
        try {
            // Bootstrap Hyperdrive and handle request
            $environment = Env::isDebug() ? 'development' : 'production';
            $hyperdrive = Hyperdrive::boost($environment);

            return $hyperdrive->warp();
        } catch (\Throwable $e) {
            if (Env::isDebug()) {
                return \Hyperdrive\Http\Response::json([
                    'error' => 'Internal Server Error',
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ], 500);
            } else {
                return \Hyperdrive\Http\Response::json([
                    'error' => 'Internal Server Error'
                ], 500);
            }
        }
    }

    private function sendOpenSwooleResponse(\OpenSwoole\Http\Response $openSwooleResponse, \Hyperdrive\Http\Response $hyperdriveResponse): void
    {
        // Set status code
        $openSwooleResponse->status($hyperdriveResponse->getStatus());

        // Set headers
        foreach ($hyperdriveResponse->getHeaders() as $name => $value) {
            $openSwooleResponse->header($name, $value);
        }

        // Add OpenSwoole specific headers
        $openSwooleResponse->header('X-Powered-By', 'Hyperdrive/OpenSwoole');
        $openSwooleResponse->header('X-Coroutine-ID', \OpenSwoole\Coroutine::getCid());

        // Send response body
        $data = $hyperdriveResponse->getData();

        if (is_array($data)) {
            $openSwooleResponse->header('Content-Type', 'application/json');
            $openSwooleResponse->end(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {
            $openSwooleResponse->end((string) $data);
        }
    }

    private function handleTask($data): mixed
    {
        // Generic task handler
        if (Env::isDebug() && is_array($data)) {
            echo "📦 Processing task type: " . ($data['type'] ?? 'unknown') . "\n";
        }

        return ['status' => 'completed', 'processed_at' => time()];
    }

    public function stop(): void
    {
        if (isset($this->server)) {
            $this->server->stop();
        }
    }

    public function reload(): void
    {
        if (isset($this->server)) {
            $this->server->reload();
        }
    }

    public function getInfo(): array
    {
        $info = [
            'type' => 'openswoole',
            'description' => 'OpenSwoole coroutine server',
            'version' => phpversion('openswoole'),
            'host' => Env::getHost(),
            'port' => Env::getPort(),
            'workers' => swoole_cpu_num() * 2,
            'coroutines' => true,
            'tasks' => true,
        ];

        if (isset($this->server)) {
            $stats = $this->server->stats();
            $info['current_connections'] = $stats['connection_num'] ?? 0;
            $info['total_requests'] = $stats['request_count'] ?? 0;
        }

        return $info;
    }
}

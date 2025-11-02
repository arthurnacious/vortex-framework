<?php

declare(strict_types=1);

namespace Hyperdrive\Server;

use Hyperdrive\Support\Env;
use Hyperdrive\Hyperdrive;
use OpenSwoole\Constant;
use OpenSwoole\Util;

class OpenSwooleServer implements ServerInterface
{
    private \OpenSwoole\Http\Server $server;
    private float $serverStartTime;
    private int $cores;

    public function start(): void
    {
        $this->serverStartTime = microtime(true);
        $this->cores = Util::getCPUNum();

        $host = Env::getHost();
        $port = Env::getPort();

        $this->server = new \OpenSwoole\Http\Server($host, $port, 1, Constant::SOCK_TCP);

        $this->configureServer();
        $this->setupHandlers();

        echo "🚀 Hyperdrive OpenSwoole Server starting...\n";
        echo "📍 Server: http://{$host}:{$port}\n";
        echo "⚡ Engine: OpenSwoole " . phpversion('openswoole') . "\n";
        echo "🔧 Mode: " . (Env::isDebug() ? 'Development' : 'Production') . "\n";
        echo "🔄 Workers: " . ($this->cores * 2) . " (CPU: " . $this->cores . ")\n";
        echo "📋 Press Ctrl+C to stop the server\n\n";

        $this->server->start();
    }

    private function configureServer(): void
    {
        $this->server->set([
            'worker_num' => $this->cores * 2,
            'max_request' => 10000,
            'max_conn' => 100000,
            'enable_coroutine' => true,
            'max_coroutine' => 300000,
            'log_file' => dirname(__DIR__, 2) . '/storage/logs/openswoole.log',
            'log_level' => Env::isDebug() ? Constant::LOG_DEBUG : Constant::LOG_INFO,
            'enable_static_handler' => true,
            'document_root' => dirname(__DIR__, 2) . '/public',
            'static_handler_locations' => ['/css', '/js', '/images'],
            'reactor_num' => $this->cores * 2,
            'task_worker_num' => $this->cores,
            'task_enable_coroutine' => true,
            'buffer_output_size' => 32 * 1024 * 1024,
            'http_compression' => true,
            'http_compression_level' => 6,
            'package_max_length' => 100 * 1024 * 1024,
        ]);
    }

    private function setupHandlers(): void
    {
        $this->server->on('start', function () {
            $uptime = round(microtime(true) - $this->serverStartTime, 3);
            echo "✅ OpenSwoole Server started successfully ({$uptime}s)\n";
            echo "🔄 Worker processes: " . ($this->cores * 2) . "\n";

            if (Env::isDebug()) {
                echo "🐛 Debug mode: ENABLED\n";
            }
        });

        $this->server->on('shutdown', function () {
            echo "🛑 OpenSwoole Server stopped\n";
        });

        $this->server->on('workerstart', function ($server, int $workerId) {
            if (Env::isDebug()) {
                echo "👷 Worker #{$workerId} started\n";
            }
        });

        $this->server->on('workerstop', function ($server, int $workerId) {
            if (Env::isDebug()) {
                echo "👷 Worker #{$workerId} stopped\n";
            }
        });

        $this->server->on('request', function ($openSwooleRequest, $openSwooleResponse) {
            $hyperdriveRequest = $this->createRequestFromOpenSwoole($openSwooleRequest);
            $hyperdriveResponse = $this->handleRequest($hyperdriveRequest);
            $this->sendOpenSwooleResponse($openSwooleResponse, $hyperdriveResponse);
        });

        $this->server->on('task', function ($server, $taskId, $reactorId, $data) {
            return $this->handleTask($data);
        });

        $this->server->on('finish', function ($server, $taskId, $data) {
            if (Env::isDebug()) {
                echo "✅ Task #{$taskId} completed\n";
            }
        });
    }

    private function createRequestFromOpenSwoole($openSwooleRequest): \Hyperdrive\Http\Request
    {
        $method = $openSwooleRequest->server['request_method'] ?? 'GET';
        $uri = $openSwooleRequest->server['request_uri'] ?? '/';
        $headers = $openSwooleRequest->header ?? [];
        $get = $openSwooleRequest->get ?? [];
        $post = $openSwooleRequest->post ?? [];
        $files = $openSwooleRequest->files ?? [];
        $cookies = $openSwooleRequest->cookie ?? [];

        $data = array_merge($get, $post);
        $request = new \Hyperdrive\Http\Request($method, $uri, $headers, $data, $get);

        foreach ($files as $key => $file) {
            $request->setAttribute("file_{$key}", $file);
        }

        foreach ($cookies as $key => $value) {
            $request->setAttribute("cookie_{$key}", $value);
        }

        $request->setAttribute('coroutine_id', \OpenSwoole\Coroutine::getCid());

        return $request;
    }

    private function handleRequest(\Hyperdrive\Http\Request $request): \Hyperdrive\Http\Response
    {
        try {
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

    private function sendOpenSwooleResponse($openSwooleResponse, \Hyperdrive\Http\Response $hyperdriveResponse): void
    {
        $openSwooleResponse->status($hyperdriveResponse->getStatus());

        foreach ($hyperdriveResponse->getHeaders() as $name => $value) {
            $openSwooleResponse->header($name, $value);
        }

        $openSwooleResponse->header('X-Powered-By', 'Hyperdrive/OpenSwoole');
        $openSwooleResponse->header('X-Coroutine-ID', (string)\OpenSwoole\Coroutine::getCid());

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
        if (Env::isDebug() && is_array($data)) {
            echo "📦 Processing task type: " . ($data['type'] ?? 'unknown') . "\n";
        }

        return ['status' => 'completed', 'processed_at' => time()];
    }

    public function stop(): void
    {
        if (isset($this->server)) {
            $this->server->stop(-1, true);
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
            'workers' => $this->cores * 2,
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

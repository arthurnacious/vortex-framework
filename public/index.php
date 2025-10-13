<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Hyperdrive\Hyperdrive;

// Start timing
$startTime = microtime(true);

$hyperdrive = new Hyperdrive();

// Get response
$response = $hyperdrive->warp();

// Add total framework time
$response['framework_time_ms'] = round((microtime(true) - $startTime) * 1000, 2);

// Output as JSON
header('Content-Type: application/json; charset=utf-8');
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
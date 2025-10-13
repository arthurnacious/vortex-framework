<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Hyperdrive\Hyperdrive;

// Start framework timing
$frameworkStartTime = microtime(true);

// Boost the application with kernel
$hyperdrive = Hyperdrive::boost('development');
$response = $hyperdrive->warp();

// Add total framework time
$frameworkTime = round((microtime(true) - $frameworkStartTime) * 1000, 2);

// Get response data and add framework timing
$responseData = $response->getData();
$responseData['framework_time_ms'] = $frameworkTime;
$responseData['kernel_booted'] = $hyperdrive->getKernel()->isBootstrapped();

// Create new response with all timing data
$finalResponse = \Hyperdrive\Http\Response::json($responseData);

// Send response
$finalResponse->send();
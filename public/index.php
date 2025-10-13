<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Hyperdrive\Hyperdrive;

// Start framework timing
$frameworkStartTime = microtime(true);

$hyperdrive = new Hyperdrive();
$response = $hyperdrive->warp();

// Add framework time to response data
$frameworkTime = round((microtime(true) - $frameworkStartTime) * 1000, 2);

// Get current response data and add framework time
$responseData = $response->getData();
$responseData['framework_time_ms'] = $frameworkTime;

// Create new response with updated data
$finalResponse = \Hyperdrive\Http\Response::json($responseData);

// Send the response
$finalResponse->send();
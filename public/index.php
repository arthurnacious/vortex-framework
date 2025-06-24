<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$app = new V8\Core\Application(dirname(__DIR__));
$app->run();

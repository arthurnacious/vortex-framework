<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$app = new V8\Core\Application(dirname(__DIR__));
$app->registerModules(require __DIR__ . '/../config/modules.php');
$app->run();

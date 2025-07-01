<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

\V8\Core\Environment::load(__DIR__ . '/..');

$app = new V8\Core\Application(dirname(__DIR__));
$app->registerModules(require __DIR__ . '/../config/modules.php');
$app->run();

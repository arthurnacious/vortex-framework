<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

\V8\Environment::load(__DIR__ . '/..');

$app = new V8\Application(dirname(__DIR__));


$app->registerModules(require __DIR__ . '/../src/bootstrap.php');
$app->run();

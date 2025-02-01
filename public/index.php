<?php


require __DIR__ . '/../vendor/autoload.php';


use Framework\Routing\Router;

$container = require __DIR__ . '/../bootstrap/app.php';

$router = new Router($container);

$router->dispatch();
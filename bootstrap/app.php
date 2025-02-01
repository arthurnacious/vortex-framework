<?php
use Framework\Container\Container;
use App\Users\UsersModule;

$container = new Container();

// Register the Users module
$usersModule = new UsersModule();
$usersModule->register($container);
$container->registerModule($usersModule);

return $container;
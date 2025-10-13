<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Hyperdrive\Hyperdrive;

$hyperdrive = new Hyperdrive();

echo "Hyperdrive starting...\n";
echo Hyperdrive::boost() . "\n";
echo $hyperdrive->warp() . "\n";
echo "Basic class working!\n";
<?php

declare(strict_types=1);

namespace Hyperdrive\Server;

interface ServerInterface
{
    public function start(): void;
    public function stop(): void;
    public function reload(): void;
    public function getInfo(): array;
}

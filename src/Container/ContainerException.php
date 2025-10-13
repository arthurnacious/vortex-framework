<?php

declare(strict_types=1);

namespace Hyperdrive\Container;

use Psr\Container\ContainerExceptionInterface;

class ContainerException extends \Exception implements ContainerExceptionInterface
{
}
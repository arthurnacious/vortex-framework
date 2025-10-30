<?php

declare(strict_types=1);

namespace Hyperdrive\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Module
{
    public function __construct(
        public string $name = '',
        public string $version = '',
        public array $imports = [],
        public array $providers = [],
        public array $listeners = [],
        public array $routes = []
    ) {}
}

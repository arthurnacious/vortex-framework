<?php

declare(strict_types=1);

namespace Hyperdrive\ServiceProvider;

use Hyperdrive\Contracts\ServiceProvider\ServiceProviderInterface;
use Hyperdrive\Contracts\Container\ContainerInterface;

class ServiceProviderRegistry
{
    private array $providers = [];
    private array $booted = [];
    private array $deferred = [];
    private ?ContainerInterface $container = null;

    public function register(string $providerClass, ?ContainerInterface $container = null): void
    {
        if (!class_exists($providerClass)) {
            throw new \InvalidArgumentException("Service provider {$providerClass} does not exist");
        }

        if (!is_subclass_of($providerClass, ServiceProviderInterface::class)) {
            throw new \InvalidArgumentException("Service provider must implement ServiceProviderInterface");
        }

        if (isset($this->providers[$providerClass])) {
            return; // Already registered
        }

        // Store container for later use
        if ($container) {
            $this->container = $container;
        }

        /** @var ServiceProviderInterface $provider */
        $provider = new $providerClass();
        $this->providers[$providerClass] = $provider;

        // Register immediately if not deferred and we have a container
        if (!$provider->isDeferred() && $this->container) {
            $this->registerProvider($provider);
        } elseif ($provider->isDeferred()) {
            $this->deferred[$providerClass] = $provider;
        }
    }

    public function boot(ContainerInterface $container): void
    {
        $this->container = $container;

        // Register any non-deferred providers that weren't registered yet
        foreach ($this->providers as $providerClass => $provider) {
            if (!$provider->isDeferred() && !in_array($providerClass, $this->booted)) {
                $this->registerProvider($provider);
            }
        }

        // Boot all providers
        foreach ($this->providers as $providerClass => $provider) {
            if (!in_array($providerClass, $this->booted)) {
                $provider->boot($container);
                $this->booted[] = $providerClass;
            }
        }
    }

    public function loadDeferred(string $service): void
    {
        if (!$this->container) {
            return;
        }

        foreach ($this->deferred as $providerClass => $provider) {
            if (in_array($service, $provider->provides())) {
                $this->registerProvider($provider);
                $provider->boot($this->container);
                unset($this->deferred[$providerClass]);
                $this->booted[] = $providerClass;
            }
        }
    }

    private function registerProvider(ServiceProviderInterface $provider): void
    {
        if ($this->container) {
            $provider->register($this->container);
        }
    }

    public function getProviders(): array
    {
        return $this->providers;
    }

    public function getBooted(): array
    {
        return $this->booted;
    }

    public function getDeferred(): array
    {
        return $this->deferred;
    }
}
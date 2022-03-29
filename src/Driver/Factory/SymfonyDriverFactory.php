<?php

declare(strict_types=1);

namespace FriendsOfBehat\SymfonyExtension\Driver\Factory;

use Behat\Mink\Driver\BrowserKitDriver;
use Behat\MinkExtension\ServiceContainer\Driver\DriverFactory;
use FriendsOfBehat\SymfonyExtension\Driver\SymfonyDriver;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class SymfonyDriverFactory implements DriverFactory
{
    /** @var string */
    private $name;

    /** @var Reference */
    private $kernel;

    public function __construct(string $name, Reference $kernel)
    {
        $this->name = $name;
        $this->kernel = $kernel;
    }

    public function getDriverName(): string
    {
        return $this->name;
    }

    public function supportsJavascript(): bool
    {
        return false;
    }

    public function configure(ArrayNodeDefinition $builder): void
    {
    }

    public function buildDriver(array $config): Definition
    {
        if (!class_exists(BrowserKitDriver::class)) {
            throw new \RuntimeException('Install "behat/mink-browserkit-driver" in order to use the "symfony" driver.');
        }

        return new Definition(SymfonyDriver::class, [
            $this->kernel,
            '%mink.base_url%',
        ]);
    }
}

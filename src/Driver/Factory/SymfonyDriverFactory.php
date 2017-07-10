<?php

declare(strict_types=1);

/*
 * This file is part of the SymfonyExtension package.
 *
 * (c) Kamil Kokot <kamil@kokot.me>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FriendsOfBehat\SymfonyExtension\Driver\Factory;

use Behat\MinkExtension\ServiceContainer\Driver\DriverFactory;
use FriendsOfBehat\SymfonyExtension\Driver\SymfonyDriver;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class SymfonyDriverFactory implements DriverFactory
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Reference
     */
    private $kernel;

    /**
     * @param string $name
     * @param Reference $kernel
     */
    public function __construct(string $name, Reference $kernel)
    {
        $this->name = $name;
        $this->kernel = $kernel;
    }

    /**
     * {@inheritdoc}
     */
    public function getDriverName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsJavascript(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(ArrayNodeDefinition $builder): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function buildDriver(array $config): Definition
    {
        return new Definition(SymfonyDriver::class, [
            $this->kernel,
            '%mink.base_url%',
        ]);
    }
}

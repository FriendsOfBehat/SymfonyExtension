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

namespace FriendsOfBehat\SymfonyExtension\Context\Environment;

use Behat\Testwork\Environment\StaticEnvironment;
use FriendsOfBehat\SymfonyExtension\Context\Environment\Handler\ContextServiceEnvironmentHandler;

/**
 * @see ContextServiceEnvironmentHandler
 */
final class UninitialisedContextServiceEnvironment extends StaticEnvironment implements ContextServiceEnvironment
{
    /** @var string[] */
    private $contextServices = [];

    public function registerContextService(string $serviceId, string $serviceClass): void
    {
        $this->contextServices[$serviceId] = $serviceClass;
    }

    public function getContextServices(): array
    {
        return array_keys($this->contextServices);
    }

    public function hasContexts(): bool
    {
        return count($this->contextServices) > 0;
    }

    public function getContextClasses(): array
    {
        return array_values($this->contextServices);
    }

    public function hasContextClass($class): bool
    {
        return in_array($class, $this->contextServices, true);
    }
}

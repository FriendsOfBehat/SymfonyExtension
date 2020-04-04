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

use Behat\Behat\Context\Environment\ContextEnvironment;
use Behat\Testwork\Environment\StaticEnvironment;
use Behat\Testwork\Suite\Suite;
use FriendsOfBehat\SymfonyExtension\Context\Environment\Handler\ContextServiceEnvironmentHandler;

/**
 * @see ContextServiceEnvironmentHandler
 */
final class UninitializedSymfonyExtensionEnvironment extends StaticEnvironment implements SymfonyExtensionEnvironment
{
    /** @var string[] */
    private $contexts;

    /** @var ContextEnvironment */
    private $delegatedEnvironment;

    public function __construct(Suite $suite, array $contexts, ContextEnvironment $delegatedEnvironment)
    {
        parent::__construct($suite);

        $this->contexts = $contexts;
        $this->delegatedEnvironment = $delegatedEnvironment;
    }

    public function getServices(): array
    {
        return array_keys($this->contexts);
    }

    public function hasContexts(): bool
    {
        return count($this->contexts) > 0 || $this->delegatedEnvironment->hasContexts();
    }

    public function getContextClasses(): array
    {
        return array_merge(array_values($this->contexts), $this->delegatedEnvironment->getContextClasses());
    }

    public function hasContextClass($class): bool
    {
        return in_array($class, $this->contexts, true) || $this->delegatedEnvironment->hasContextClass($class);
    }

    public function getDelegatedEnvironment(): ContextEnvironment
    {
        return $this->delegatedEnvironment;
    }
}

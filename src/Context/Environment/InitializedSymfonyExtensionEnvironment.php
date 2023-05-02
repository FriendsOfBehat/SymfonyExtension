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

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Exception\ContextNotFoundException;
use Behat\Testwork\Call\Callee;
use Behat\Testwork\Suite\Suite;
use FriendsOfBehat\SymfonyExtension\Context\Environment\Handler\ContextServiceEnvironmentHandler;

/**
 * @see ContextServiceEnvironmentHandler
 */
final class InitializedSymfonyExtensionEnvironment implements SymfonyExtensionEnvironment
{
    /** @var Suite */
    private $suite;

    /**
     * @var array<class-string<Context>, Context>
     * @psalm-var class-string-map<T as Context, T>
     */
    private $contexts = [];

    public function __construct(Suite $suite)
    {
        $this->suite = $suite;
    }

    public function registerContext(Context $context): void
    {
        $this->contexts[get_class($context)] = $context;
    }

    public function getSuite(): Suite
    {
        return $this->suite;
    }

    public function bindCallee(Callee $callee): callable
    {
        $callable = $callee->getCallable();

        if (is_array($callable) && $callee->isAnInstanceMethod()) {
            return [$this->getContext($callable[0]), $callable[1]];
        }

        return $callable;
    }

    public function hasContexts(): bool
    {
        return count($this->contexts) > 0;
    }

    public function getContextClasses(): array
    {
        return array_keys($this->contexts);
    }

    public function hasContextClass($class): bool
    {
        return isset($this->contexts[$class]);
    }

    /**
     * @see http://behat.org/en/latest/cookbooks/accessing_contexts_from_each_other.html
     *
     * @template T of Context
     *
     * @param class-string<T> $class
     *
     * @return T
     *
     * @throws ContextNotFoundException
     */
    public function getContext(string $class): Context
    {
        if (!isset($this->contexts[$class])) {
            throw new ContextNotFoundException(sprintf(
                '`%s` context is not found in the suite environment. Have you registered it?',
                $class,
            ), $class);
        }

        return $this->contexts[$class];
    }
}

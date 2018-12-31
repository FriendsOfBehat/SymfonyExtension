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

namespace FriendsOfBehat\SymfonyExtension\Context\Environment\Handler;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Initializer\ContextInitializer;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Environment\Exception\EnvironmentIsolationException;
use Behat\Testwork\Environment\Handler\EnvironmentHandler;
use Behat\Testwork\Suite\Exception\SuiteConfigurationException;
use Behat\Testwork\Suite\Suite;
use FriendsOfBehat\SymfonyExtension\Bundle\FriendsOfBehatSymfonyExtensionBundle;
use FriendsOfBehat\SymfonyExtension\Context\Environment\InitialisedContextServiceEnvironment;
use FriendsOfBehat\SymfonyExtension\Context\Environment\UninitialisedContextServiceEnvironment;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

final class ContextServiceEnvironmentHandler implements EnvironmentHandler
{
    /** @var KernelInterface */
    private $symfonyKernel;

    /** @var ContextInitializer[] */
    private $contextInitializers = [];

    public function __construct(KernelInterface $symfonyKernel)
    {
        $this->symfonyKernel = $symfonyKernel;
    }

    public function supportsSuite(Suite $suite): bool
    {
        return $suite->hasSetting('contexts');
    }

    public function buildEnvironment(Suite $suite): Environment
    {
        $environment = new UninitialisedContextServiceEnvironment($suite);
        foreach ($this->getSuiteContextsServices($suite) as $contextId) {
            $environment->registerContextService($contextId, $this->getContextClass($contextId));
        }

        return $environment;
    }

    public function supportsEnvironmentAndSubject(Environment $environment, $testSubject = null): bool
    {
        return $environment instanceof UninitialisedContextServiceEnvironment;
    }

    /**
     * @throws EnvironmentIsolationException
     */
    public function isolateEnvironment(Environment $uninitializedEnvironment, $testSubject = null): Environment
    {
        /** @var UninitialisedContextServiceEnvironment $uninitializedEnvironment */
        $this->assertEnvironmentCanBeIsolated($uninitializedEnvironment, $testSubject);

        $environment = new InitialisedContextServiceEnvironment($uninitializedEnvironment->getSuite());
        foreach ($uninitializedEnvironment->getContextServices() as $contextId) {
            /** @var Context $context */
            $context = $this->getContext($contextId);
            $this->initializeInstance($context);
            $environment->registerContext($context);
        }

        return $environment;
    }

    public function registerContextInitializer(ContextInitializer $initializer): void
    {
        $this->contextInitializers[] = $initializer;
    }

    /**
     * @return string[]
     *
     * @throws SuiteConfigurationException If "contexts" setting is not an array
     */
    private function getSuiteContextsServices(Suite $suite): array
    {
        $contextsServices = $suite->getSetting('contexts');

        if (!is_array($contextsServices)) {
            throw new SuiteConfigurationException(sprintf(
                '"contexts" setting of the "%s" suite is expected to be an array, %s given.',
                $suite->getName(),
                gettype($contextsServices)
            ), $suite->getName());
        }

        return $contextsServices;
    }

    /**
     * @throws EnvironmentIsolationException
     */
    private function assertEnvironmentCanBeIsolated(Environment $uninitializedEnvironment, $testSubject): void
    {
        if (!$this->supportsEnvironmentAndSubject($uninitializedEnvironment, $testSubject)) {
            throw new EnvironmentIsolationException(sprintf(
                '"%s" does not support isolation of "%s" environment.',
                static::class,
                get_class($uninitializedEnvironment)
            ), $uninitializedEnvironment);
        }
    }

    private function initializeInstance(Context $context): void
    {
        foreach ($this->contextInitializers as $initializer) {
            $initializer->initializeContext($context);
        }
    }

    private function getContextClass(string $contextId): string
    {
        if ($this->getContainer()->has($contextId)) {
            return get_class($this->getContainer()->get($contextId));
        }

        $class = '\\' . ltrim($contextId, '\\');

        if (class_exists($class)) {
            return $class;
        }

        throw new \Exception('wtf?');
    }

    private function getContext(string $contextId): Context
    {
        if ($this->getContainer()->has($contextId)) {
            return $this->getContainer()->get($contextId);
        }

        $class = '\\' . ltrim($contextId, '\\');

        if (class_exists($class)) {
            return new $class();
        }

        throw new \Exception('wtf?');
    }

    private function getContainer(): ContainerInterface
    {
        try {
            $this->symfonyKernel->getBundle('FriendsOfBehatSymfonyExtensionBundle');
        } catch (\InvalidArgumentException $exception) {
            throw new \RuntimeException(sprintf(
                'Kernel "%s" used by Behat with "%s" environment and debug %s needs to have "%s" bundle registered.',
                get_class($this->symfonyKernel),
                $this->symfonyKernel->getEnvironment(),
                $this->symfonyKernel->isDebug() ? 'enabled' : 'disabled',
                FriendsOfBehatSymfonyExtensionBundle::class
            ));
        }

        return $this->symfonyKernel->getContainer();
    }
}

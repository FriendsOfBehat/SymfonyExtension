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
use Behat\Behat\Context\Environment\ContextEnvironment;
use Behat\Behat\Context\Environment\InitializedContextEnvironment;
use Behat\Behat\Context\Initializer\ContextInitializer;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Environment\Exception\EnvironmentIsolationException;
use Behat\Testwork\Environment\Handler\EnvironmentHandler;
use Behat\Testwork\Suite\Exception\SuiteConfigurationException;
use Behat\Testwork\Suite\GenericSuite;
use Behat\Testwork\Suite\Suite;
use FriendsOfBehat\SymfonyExtension\Bundle\FriendsOfBehatSymfonyExtensionBundle;
use FriendsOfBehat\SymfonyExtension\Context\Environment\InitializedSymfonyExtensionEnvironment;
use FriendsOfBehat\SymfonyExtension\Context\Environment\UninitializedSymfonyExtensionEnvironment;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

final class ContextServiceEnvironmentHandler implements EnvironmentHandler
{
    /** @var KernelInterface */
    private $symfonyKernel;

    /** @var EnvironmentHandler */
    private $decoratedEnvironmentHandler;

    /** @var ContextInitializer[] */
    private $contextInitializers = [];

    public function __construct(KernelInterface $symfonyKernel, EnvironmentHandler $decoratedEnvironmentHandler)
    {
        $this->symfonyKernel = $symfonyKernel;
        $this->decoratedEnvironmentHandler = $decoratedEnvironmentHandler;
    }

    public function registerContextInitializer(ContextInitializer $contextInitializer): void
    {
        $this->contextInitializers[] = $contextInitializer;
    }

    public function supportsSuite(Suite $suite): bool
    {
        return $suite->hasSetting('contexts');
    }

    public function buildEnvironment(Suite $suite): Environment
    {
        $symfonyContexts = [];

        foreach ($this->getSuiteContextsServices($suite) as $serviceId) {
            if (!$this->getContainer()->has($serviceId)) {
                continue;
            }

            /** @var object $service */
            $service = $this->getContainer()->get($serviceId);

            $symfonyContexts[$serviceId] = get_class($service);
        }

        $delegatedSuite = $this->cloneSuiteWithoutContexts($suite, array_keys($symfonyContexts));

        /** @var ContextEnvironment $delegatedEnvironment */
        $delegatedEnvironment = $this->decoratedEnvironmentHandler->buildEnvironment($delegatedSuite);

        return new UninitializedSymfonyExtensionEnvironment($suite, $symfonyContexts, $delegatedEnvironment);
    }

    public function supportsEnvironmentAndSubject(Environment $environment, $testSubject = null): bool
    {
        return $environment instanceof UninitializedSymfonyExtensionEnvironment;
    }

    /**
     * @throws EnvironmentIsolationException
     */
    public function isolateEnvironment(Environment $environment, $testSubject = null): Environment
    {
        $this->assertEnvironmentCanBeIsolated($environment, $testSubject);

        $isolatedEnvironment = new InitializedSymfonyExtensionEnvironment($environment->getSuite());

        foreach ($environment->getServices() as $serviceId) {
            /** @var Context $context */
            $context = $this->getContainer()->get($serviceId);

            $this->initializeContext($context);

            $isolatedEnvironment->registerContext($context);
        }

        /** @var InitializedContextEnvironment $delegatedEnvironment */
        $delegatedEnvironment = $this->decoratedEnvironmentHandler->isolateEnvironment($environment->getDelegatedEnvironment());

        foreach ($delegatedEnvironment->getContexts() as $context) {
            $isolatedEnvironment->registerContext($context);
        }

        return $isolatedEnvironment;
    }

    private function initializeContext(Context $context): void
    {
        foreach ($this->contextInitializers as $contextInitializer) {
            $contextInitializer->initializeContext($context);
        }
    }

    /**
     * @return string[]
     *
     * @throws SuiteConfigurationException If "contexts" setting is not an array
     */
    private function getSuiteContextsServices(Suite $suite): array
    {
        $contexts = $suite->getSetting('contexts');

        if (!is_array($contexts)) {
            throw new SuiteConfigurationException(sprintf(
                '"contexts" setting of the "%s" suite is expected to be an array, %s given.',
                $suite->getName(),
                gettype($contexts),
            ), $suite->getName());
        }

        return array_map([$this, 'normalizeContext'], $contexts);
    }

    private function cloneSuiteWithoutContexts(Suite $suite, array $contextsToRemove): Suite
    {
        $contexts = $suite->getSetting('contexts');

        if (!is_array($contexts)) {
            throw new SuiteConfigurationException(sprintf(
                '"contexts" setting of the "%s" suite is expected to be an array, %s given.',
                $suite->getName(),
                gettype($contexts),
            ), $suite->getName());
        }

        $contexts = array_filter($contexts, function ($context) use ($contextsToRemove): bool {
            return !in_array($this->normalizeContext($context), $contextsToRemove, true);
        });

        return new GenericSuite($suite->getName(), array_merge($suite->getSettings(), ['contexts' => $contexts]));
    }

    private function normalizeContext($context): string
    {
        if (is_array($context)) {
            return current(array_keys($context));
        }

        if (is_string($context)) {
            return $context;
        }

        throw new \Exception();
    }

    /**
     * @psalm-assert UninitializedSymfonyExtensionEnvironment $uninitializedEnvironment
     *
     * @throws EnvironmentIsolationException
     */
    private function assertEnvironmentCanBeIsolated(Environment $uninitializedEnvironment, $testSubject): void
    {
        if (!$this->supportsEnvironmentAndSubject($uninitializedEnvironment, $testSubject)) {
            throw new EnvironmentIsolationException(sprintf(
                '"%s" does not support isolation of "%s" environment.',
                static::class,
                get_class($uninitializedEnvironment),
            ), $uninitializedEnvironment);
        }
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
                FriendsOfBehatSymfonyExtensionBundle::class,
            ));
        }

        return $this->symfonyKernel->getContainer();
    }
}

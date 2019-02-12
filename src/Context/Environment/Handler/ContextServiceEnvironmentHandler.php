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

use Behat\Behat\Context\Argument\ArgumentResolverFactory;
use Behat\Behat\Context\Argument\NullFactory;
use Behat\Behat\Context\Argument\SuiteScopedResolverFactory;
use Behat\Behat\Context\Argument\SuiteScopedResolverFactoryAdapter;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\ContextClass\ClassResolver;
use Behat\Behat\Context\ContextFactory;
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

    /** @var ClassResolver[] */
    private $classResolvers = [];

    /** @var ContextFactory */
    private $contextFactory;

    /** @var ArgumentResolverFactory */
    private $resolverFactory;

    /**
     * @param ArgumentResolverFactory|SuiteScopedResolverFactory $resolverFactory
     */
    public function __construct(KernelInterface $symfonyKernel, ContextFactory $factory, $resolverFactory = null)
    {
        $this->symfonyKernel = $symfonyKernel;

        $this->contextFactory = $factory;

        if ($resolverFactory && !$resolverFactory instanceof ArgumentResolverFactory) {
            $resolverFactory = new SuiteScopedResolverFactoryAdapter($resolverFactory);
        }

        $this->resolverFactory = $resolverFactory ?: new NullFactory();
    }

    public function supportsSuite(Suite $suite): bool
    {
        return $suite->hasSetting('contexts');
    }

    public function buildEnvironment(Suite $suite): Environment
    {
        $environment = new UninitialisedContextServiceEnvironment($suite);
        foreach ($this->getSuiteContextsServices($suite) as [$contextId, $contextArguments]) {
            $environment->registerContextService($contextId, $this->getContextClass($contextId), $contextArguments);
        }

        return $environment;
    }

    public function supportsEnvironmentAndSubject(Environment $environment, $testSubject = null): bool
    {
        return $environment instanceof UninitialisedContextServiceEnvironment;
    }

    /**
     * @param UninitialisedContextServiceEnvironment $uninitializedEnvironment
     *
     * @throws EnvironmentIsolationException
     */
    public function isolateEnvironment(Environment $uninitializedEnvironment, $testSubject = null): Environment
    {
        $this->assertEnvironmentCanBeIsolated($uninitializedEnvironment, $testSubject);

        $environment = new InitialisedContextServiceEnvironment($uninitializedEnvironment->getSuite());
        $resolvers = $this->resolverFactory->createArgumentResolvers($environment);

        foreach ($uninitializedEnvironment->getContextServicesWithArguments() as $contextId => $arguments) {
            /** @var Context $context */
            $context = $this->getContext($contextId, $arguments, $resolvers);
            $environment->registerContext($context);
        }

        return $environment;
    }

    public function registerClassResolver(ClassResolver $classResolver): void
    {
        $this->classResolvers[] = $classResolver;
    }

    /**
     * @return array[]
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

        return array_map(
            function ($context): array {
                $class = $context;
                $arguments = [];

                if (is_array($context)) {
                    $class = current(array_keys($context));
                    $arguments = $context[$class];
                }

                return [$class, $arguments];
            },
            $contextsServices
        );
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

    private function resolveContextId(string $contextId): string
    {
        foreach ($this->classResolvers as $resolver) {
            if ($resolver->supportsClass($contextId)) {
                return $resolver->resolveClass($contextId);
            }
        }

        return $contextId;
    }

    private function getContextClass(string $contextId): string
    {
        $contextId = $this->resolveContextId($contextId);

        if ($this->getContainer()->has($contextId)) {
            return get_class($this->getContainer()->get($contextId));
        }

        $class = '\\' . ltrim($contextId, '\\');

        if (class_exists($class)) {
            return $class;
        }

        throw new \DomainException(sprintf('There is no service or class "%s".', $contextId));
    }

    private function getContext(string $contextId, array $arguments = [], array $resolvers = []): Context
    {
        $contextId = $this->resolveContextId($contextId);

        $class = '\\' . ltrim($contextId, '\\');

        if ($this->getContainer()->has($contextId)) {
            $context = $this->getContainer()->get($contextId);
        } elseif (class_exists($class)) {
            $context = $this->contextFactory->createContext($class, $arguments, $resolvers);
        } else {
            throw new \DomainException(sprintf('There is no service or class "%s".', $contextId));
        }

        if (!$context instanceof Context) {
            throw new \DomainException(sprintf(
                'Context "%s" referenced as "%s" needs to implement "%s".',
                get_class($context),
                $contextId,
                Context::class
            ));
        }

        return $context;
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

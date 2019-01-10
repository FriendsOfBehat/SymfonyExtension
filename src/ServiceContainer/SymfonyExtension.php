<?php

declare(strict_types=1);

namespace FriendsOfBehat\SymfonyExtension\ServiceContainer;

use Behat\Behat\Context\ServiceContainer\ContextExtension;
use Behat\Mink\Session;
use Behat\MinkExtension\ServiceContainer\MinkExtension;
use Behat\Testwork\Environment\ServiceContainer\EnvironmentExtension;
use Behat\Testwork\EventDispatcher\ServiceContainer\EventDispatcherExtension;
use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use FriendsOfBehat\SymfonyExtension\Context\Environment\Handler\ContextServiceEnvironmentHandler;
use FriendsOfBehat\SymfonyExtension\Driver\Factory\SymfonyDriverFactory;
use FriendsOfBehat\SymfonyExtension\Listener\KernelOrchestrator;
use FriendsOfBehat\SymfonyExtension\Mink\MinkParameters;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\DependencyInjection\Reference;

final class SymfonyExtension implements Extension
{
    /**
     * Kernel used inside Behat contexts or to create services injected to them.
     * Container is rebuilt before every scenario.
     */
    public const KERNEL_ID = 'fob_symfony.kernel';

    /**
     * Kernel used by Symfony driver to isolate web container from contexts' container.
     * Container is rebuilt before every request.
     */
    private const DRIVER_KERNEL_ID = 'fob_symfony.driver_kernel';

    /** @var bool */
    private $minkExtensionFound = false;

    public function getConfigKey(): string
    {
        return 'fob_symfony';
    }

    public function initialize(ExtensionManager $extensionManager): void
    {
        $this->registerMinkDriver($extensionManager);
    }

    public function configure(ArrayNodeDefinition $builder): void
    {
        $builder
            ->children()
                ->arrayNode('kernel')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('path')->defaultNull()->end()
                        ->scalarNode('class')->defaultNull()->end()
                        ->scalarNode('environment')->defaultValue('test')->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    public function load(ContainerBuilder $container, array $config): void
    {
        $this->loadKernel($container, $this->processKernelConfiguration($config['kernel']));
        $this->loadDriverKernel($container);

        $this->loadKernelRebooter($container);

        $this->loadEnvironmentHandler($container);

        if ($this->minkExtensionFound) {
            $this->loadMinkDefaultSession($container);
            $this->loadMinkParameters($container);
        }
    }

    public function process(ContainerBuilder $container): void
    {
    }

    private function registerMinkDriver(ExtensionManager $extensionManager): void
    {
        /** @var MinkExtension|null $minkExtension */
        $minkExtension = $extensionManager->getExtension('mink');
        if (null === $minkExtension) {
            return;
        }

        $minkExtension->registerDriverFactory(new SymfonyDriverFactory('symfony', new Reference(self::DRIVER_KERNEL_ID)));

        $this->minkExtensionFound = true;
    }

    private function loadKernel(ContainerBuilder $container, array $config): void
    {
        $definition = new Definition($config['class'], [
            $config['environment'],
            true,
        ]);
        $definition->addMethodCall('boot');
        $definition->setPublic(true);

        if ($config['path'] !== null) {
            $definition->setFile($config['path']);
        }

        $container->setDefinition(self::KERNEL_ID, $definition);
    }

    private function loadDriverKernel(ContainerBuilder $container): void
    {
        $container->setDefinition(self::DRIVER_KERNEL_ID, $container->findDefinition(self::KERNEL_ID));
    }

    private function loadKernelRebooter(ContainerBuilder $container): void
    {
        $definition = new Definition(KernelOrchestrator::class, [new Reference(self::KERNEL_ID), $container]);
        $definition->addTag(EventDispatcherExtension::SUBSCRIBER_TAG);

        $container->setDefinition('fob_symfony.kernel_orchestrator', $definition);
    }

    private function loadEnvironmentHandler(ContainerBuilder $container): void
    {
        $definition = new Definition(ContextServiceEnvironmentHandler::class, [
            new Reference(self::KERNEL_ID),
        ]);
        $definition->addTag(EnvironmentExtension::HANDLER_TAG, ['priority' => 128]);

        foreach ($container->findTaggedServiceIds(ContextExtension::INITIALIZER_TAG) as $serviceId => $tags) {
            $definition->addMethodCall('registerContextInitializer', [$container->getDefinition($serviceId)]);
        }

        $container->setDefinition('fob_symfony.environment_handler.context_service', $definition);
    }

    private function loadMinkDefaultSession(ContainerBuilder $container): void
    {
        $minkDefaultSessionDefinition = new Definition(Session::class);
        $minkDefaultSessionDefinition->setPublic(true);
        $minkDefaultSessionDefinition->setFactory([new Reference('mink'), 'getSession']);

        $container->setDefinition('fob_symfony.mink.default_session', $minkDefaultSessionDefinition);
    }

    private function loadMinkParameters(ContainerBuilder $container): void
    {
        $minkParametersDefinition = new Definition(MinkParameters::class, [new Parameter('mink.parameters')]);
        $minkParametersDefinition->setPublic(true);

        $container->setDefinition('fob_symfony.mink.parameters', $minkParametersDefinition);
    }

    private function processKernelConfiguration(array $config): array
    {
        if ($config['class'] !== null) {
            return $config;
        }

        $autoconfigured = 0;

        if (class_exists('\App\Kernel')) {
            $config['class'] = '\App\Kernel';

            ++$autoconfigured;
        }

        if (file_exists('app/AppKernel.php')) {
            $config['class'] = '\AppKernel';
            $config['path'] = 'app/AppKernel.php';

            ++$autoconfigured;
        }

        if ($autoconfigured !== 1) {
            throw new \RuntimeException(
                'Could not autodiscover the application kernel. ' .
                'Please define it manually with "FriendsOfBehat\SymfonyExtension.kernel" configuration option.'
            );
        }

        return $config;
    }
}

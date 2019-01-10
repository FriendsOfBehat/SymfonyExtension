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
use FriendsOfBehat\SymfonyExtension\Listener\KernelRebooter;
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
        /** @var MinkExtension|null $minkExtension */
        $minkExtension = $extensionManager->getExtension('mink');
        if (null === $minkExtension) {
            return;
        }

        $minkExtension->registerDriverFactory(new SymfonyDriverFactory('symfony', new Reference(self::DRIVER_KERNEL_ID)));
        $this->minkExtensionFound = true;
    }

    public function configure(ArrayNodeDefinition $builder): void
    {
        $builder
            ->children()
                ->arrayNode('kernel')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('path')->defaultNull()->end()
                        ->scalarNode('class')->isRequired()->end()
                        ->scalarNode('env')->defaultValue('test')->cannotBeEmpty()->end()
                        ->booleanNode('debug')->defaultTrue()->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    public function load(ContainerBuilder $container, array $config): void
    {
        $this->loadKernel($container, $config['kernel']);
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

    private function loadKernel(ContainerBuilder $container, array $config): void
    {
        $definition = new Definition($config['class'], [
            $config['env'],
            (bool) $config['debug'],
        ]);
        $definition->addMethodCall('boot');
        $definition->setPublic(true);

        if (null !== $config['path']) {
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
        $definition = new Definition(KernelRebooter::class, [new Reference(self::KERNEL_ID), $container]);
        $definition->addTag(EventDispatcherExtension::SUBSCRIBER_TAG);

        $container->setDefinition(self::KERNEL_ID . '.rebooter', $definition);
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
}

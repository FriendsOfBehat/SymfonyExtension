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
use FriendsOfBehat\SymfonyExtension\Mink\Mink;
use FriendsOfBehat\SymfonyExtension\Mink\MinkParameters;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\Alias;
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
    public const DRIVER_KERNEL_ID = 'fob_symfony.driver_kernel';

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
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('bootstrap')->defaultNull()->end()
                ->arrayNode('kernel')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('path')->defaultNull()->end()
                        ->scalarNode('class')->defaultNull()->end()
                        ->scalarNode('environment')->defaultNull()->end()
                        ->booleanNode('debug')->defaultNull()->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    public function load(ContainerBuilder $container, array $config): void
    {
        $this->setupTestEnvironment($config['kernel']['environment'] ?? 'test');

        $this->loadBootstrap($this->autodiscoverBootstrap($config['bootstrap']));

        $this->loadKernel($container, $this->autodiscoverKernelConfiguration($config['kernel']));
        $this->loadDriverKernel($container);

        $this->loadKernelRebooter($container);

        $this->loadEnvironmentHandler($container);

        if ($this->minkExtensionFound) {
            $this->loadMink($container);
            $this->loadMinkDefaultSession($container);
            $this->loadMinkParameters($container);
        }
    }

    public function process(ContainerBuilder $container): void
    {
        $this->processEnvironmentHandler($container);

        if ($this->minkExtensionFound) {
            $container->getDefinition(MinkExtension::MINK_ID)->setClass(Mink::class);
        }
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
            $config['environment'] ?? $_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? 'test',
            (bool) ($config['debug'] ?? $_SERVER['APP_DEBUG'] ?? $_ENV['APP_DEBUG'] ?? true),
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
        $definition = new Definition(KernelOrchestrator::class, [new Reference(self::KERNEL_ID), new Reference(self::DRIVER_KERNEL_ID), $container]);
        $definition->addTag(EventDispatcherExtension::SUBSCRIBER_TAG);

        $container->setDefinition('fob_symfony.kernel_orchestrator', $definition);
    }

    private function loadEnvironmentHandler(ContainerBuilder $container): void
    {
        $definition = new Definition(ContextServiceEnvironmentHandler::class, [
            new Reference(self::KERNEL_ID),
            new Reference('environment.handler.context'),
        ]);
        $definition->addTag(EnvironmentExtension::HANDLER_TAG, ['priority' => 128]);

        $container->setDefinition('fob_symfony.environment_handler.context_service', $definition);
    }

    private function loadMink(ContainerBuilder $container): void
    {
        $container->setAlias('fob_symfony.mink', (new Alias('mink'))->setPublic(true));
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

    private function loadBootstrap(?string $bootstrap): void
    {
        if ($bootstrap === null) {
            return;
        }

        require_once $bootstrap;
    }

    private function setupTestEnvironment(string $fallback): void
    {
        // If there's no defined server / environment variable with an environment, default to configured fallback
        if (($_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? null) === null) {
            putenv('APP_ENV=' . $_SERVER['APP_ENV'] = $_ENV['APP_ENV'] = $fallback);
        }
    }

    private function processEnvironmentHandler(ContainerBuilder $container): void
    {
        $definition = $container->findDefinition('fob_symfony.environment_handler.context_service');
        foreach ($container->findTaggedServiceIds(ContextExtension::INITIALIZER_TAG) as $serviceId => $tags) {
            $definition->addMethodCall('registerContextInitializer', [new Reference($serviceId)]);
        }
    }

    private function autodiscoverKernelConfiguration(array $config): array
    {
        if ($config['class'] !== null) {
            return $config;
        }

        $autodiscovered = 0;

        if (class_exists('\App\Kernel')) {
            $config['class'] = '\App\Kernel';

            ++$autodiscovered;
        }

        if (file_exists('app/AppKernel.php')) {
            $config['class'] = '\AppKernel';
            $config['path'] = 'app/AppKernel.php';

            ++$autodiscovered;
        }

        if ($autodiscovered !== 1) {
            throw new \RuntimeException(
                'Could not autodiscover the application kernel. ' .
                'Please define it manually with "FriendsOfBehat\SymfonyExtension.kernel" configuration option.',
            );
        }

        return $config;
    }

    /**
     * @param string|bool|null $bootstrap
     */
    private function autodiscoverBootstrap($bootstrap): ?string
    {
        if (is_string($bootstrap)) {
            return $bootstrap;
        }

        if ($bootstrap === false) {
            return null;
        }

        $autodiscovered = 0;

        if (file_exists('config/bootstrap.php')) {
            $bootstrap = 'config/bootstrap.php';

            ++$autodiscovered;
        }

        if (file_exists('app/autoload.php')) {
            $bootstrap = 'app/autoload.php';

            ++$autodiscovered;
        }

        if ($autodiscovered === 2) {
            throw new \RuntimeException(
                'Could not autodiscover the bootstrap file. ' .
                'Please define it manually with "FriendsOfBehat\SymfonyExtension.bootstrap" configuration option. ' .
                'Setting that option to "false" disables autodiscovering.',
            );
        }

        return is_string($bootstrap) ? $bootstrap : null;
    }
}

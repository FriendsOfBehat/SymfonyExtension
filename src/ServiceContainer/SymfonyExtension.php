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
use FriendsOfBehat\SymfonyExtension\Kernel\KernelManager;
use FriendsOfBehat\SymfonyExtension\Listener\KernelOrchestrator;
use FriendsOfBehat\SymfonyExtension\Mink\MinkParameters;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
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
     * Lazy-loaded: only created when Mink driver is actually used.
     */
    public const DRIVER_KERNEL_ID = 'fob_symfony.driver_kernel';

    /**
     * KernelManager service ID - manages both kernels with explicit lifecycle.
     */
    public const KERNEL_MANAGER_ID = 'fob_symfony.kernel_manager';

    private bool $minkExtensionFound = false;

    #[\Override]
    public function getConfigKey(): string
    {
        return 'fob_symfony';
    }

    #[\Override]
    public function initialize(ExtensionManager $extensionManager): void
    {
        $this->registerMinkDriver($extensionManager);
    }

    #[\Override]
    public function configure(ArrayNodeDefinition $builder): void
    {
        $builder
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('bootstrap')->defaultNull()->end()
                ->booleanNode('debug_error_handler')
                    ->defaultFalse()
                    ->info('Enable Symfony ErrorHandler to catch notices/warnings (recommended for strict testing)')
                ->end()
                ->arrayNode('kernel')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('path')->defaultNull()->end()
                        ->scalarNode('class')->defaultNull()->end()
                        ->scalarNode('environment')->defaultNull()->end()
                        ->booleanNode('debug')->defaultNull()->end()
                        ->booleanNode('reboot')
                            ->defaultTrue()
                            ->info('Reboot kernel between scenarios for isolation (disable for DAMADoctrineTestBundle)')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    #[\Override]
    public function load(ContainerBuilder $container, array $config): void
    {
        $configuredEnv = $config['kernel']['environment'];
        $this->setupTestEnvironment($configuredEnv ?? 'test', $configuredEnv !== null);

        if ($config['debug_error_handler']) {
            $this->enableDebugErrorHandler();
        }

        $this->loadBootstrap($this->autodiscoverBootstrap($config['bootstrap'], $container->getParameterBag()));

        $kernelConfig = $this->autodiscoverKernelConfiguration($config['kernel'], $container->getParameterBag());
        $this->loadKernel($container, $kernelConfig);
        $this->loadKernelManager($container, $kernelConfig);
        $this->loadDriverKernel($container);

        $this->loadKernelOrchestrator($container);

        $this->loadEnvironmentHandler($container);

        if ($this->minkExtensionFound) {
            $this->loadMink($container);
            $this->loadMinkDefaultSession($container);
            $this->loadMinkParameters($container);
        }
    }

    #[\Override]
    public function process(ContainerBuilder $container): void
    {
        $this->processEnvironmentHandler($container);
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

    private function loadKernelManager(ContainerBuilder $container, array $config): void
    {
        // Store kernel config for lazy factory
        $kernelClass = $config['class'];
        $env = $config['environment'] ?? $_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? 'test';
        $debug = (bool) ($config['debug'] ?? $_SERVER['APP_DEBUG'] ?? $_ENV['APP_DEBUG'] ?? true);
        $path = $config['path'];

        $reboot = $config['reboot'] ?? true;

        $definition = new Definition(KernelManager::class, [
            new Reference(self::KERNEL_ID),
            static function () use ($kernelClass, $env, $debug, $path): \Symfony\Component\HttpKernel\KernelInterface {
                if ($path !== null) {
                    require_once $path;
                }

                return new $kernelClass($env, $debug);
            },
            $reboot,
        ]);
        $definition->setPublic(true);
        $definition->addMethodCall('setBehatContainer', [$container]);

        $container->setDefinition(self::KERNEL_MANAGER_ID, $definition);
    }

    private function loadDriverKernel(ContainerBuilder $container): void
    {
        // Driver kernel is fetched lazily from KernelManager
        $definition = new Definition(\Symfony\Component\HttpKernel\KernelInterface::class);
        $definition->setFactory([new Reference(self::KERNEL_MANAGER_ID), 'getDriverKernel']);
        $definition->setPublic(true);

        $container->setDefinition(self::DRIVER_KERNEL_ID, $definition);
    }

    private function loadKernelOrchestrator(ContainerBuilder $container): void
    {
        $definition = new Definition(KernelOrchestrator::class, [
            new Reference(self::KERNEL_MANAGER_ID),
        ]);
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

    private function setupTestEnvironment(string $environment, bool $force = false): void
    {
        // If environment is explicitly configured, force it (fixes #215)
        // Otherwise, only set if APP_ENV is not already defined
        if ($force || ($_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? null) === null) {
            putenv('APP_ENV=' . $_SERVER['APP_ENV'] = $_ENV['APP_ENV'] = $environment);
        }
    }

    /**
     * Enable Symfony's ErrorHandler to convert notices/warnings to exceptions.
     *
     * This makes tests stricter by catching issues that would cause problems
     * in production (fixes #148).
     */
    private function enableDebugErrorHandler(): void
    {
        if (class_exists(\Symfony\Component\ErrorHandler\Debug::class)) {
            \Symfony\Component\ErrorHandler\Debug::enable();
        } elseif (class_exists(\Symfony\Component\Debug\Debug::class)) {
            // Symfony 4.x compatibility
            \Symfony\Component\Debug\Debug::enable();
        }
    }

    private function processEnvironmentHandler(ContainerBuilder $container): void
    {
        $definition = $container->findDefinition('fob_symfony.environment_handler.context_service');
        foreach ($container->findTaggedServiceIds(ContextExtension::INITIALIZER_TAG) as $serviceId => $tags) {
            $definition->addMethodCall('registerContextInitializer', [new Reference($serviceId)]);
        }
    }

    private function autodiscoverKernelConfiguration(array $config, ParameterBag $parameterBag): array
    {
        if ($config['class'] !== null) {
            return $config;
        }

        $autodiscovered = 0;
        $basePath = $parameterBag->get('paths.base');

        if (class_exists('\App\Kernel')) {
            $config['class'] = '\App\Kernel';

            ++$autodiscovered;
        }

        // Use base path for file_exists check (fixes #89 - works from any directory)
        $legacyKernelPath = $basePath . '/app/AppKernel.php';
        if (file_exists($legacyKernelPath)) {
            $config['class'] = '\AppKernel';
            $config['path'] = $legacyKernelPath;

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
    private function autodiscoverBootstrap($bootstrap, ParameterBag $parameterBag): ?string
    {
        if (is_string($bootstrap)) {
            return $parameterBag->resolveString($bootstrap);
        }

        if ($bootstrap === false) {
            return null;
        }

        $autodiscovered = 0;
        $basePath = $parameterBag->get('paths.base');

        if (file_exists($basePath . '/config/bootstrap.php')) {
            $bootstrap = $basePath . '/config/bootstrap.php';

            ++$autodiscovered;
        }

        if (file_exists($basePath . '/app/autoload.php')) {
            $bootstrap = $basePath . '/app/autoload.php';

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

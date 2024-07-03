<?php

declare(strict_types=1);

namespace FriendsOfBehat\SymfonyExtension\Bundle\DependencyInjection;

use Behat\Behat\Context\Context;
use Behat\Mink\Mink;
use Behat\Mink\Session;
use FriendsOfBehat\SymfonyExtension\Mink\MinkParameters;
use FriendsOfBehat\SymfonyExtension\ServiceContainer\SymfonyExtension;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\HttpKernelBrowser;
use Symfony\Component\HttpKernel\KernelInterface;

final class FriendsOfBehatSymfonyExtensionExtension extends Extension implements CompilerPassInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $this->provideMinkIntegration($container);
        $this->registerBehatContainer($container);
        $this->registerDriverBehatContainer($container);

        $container->registerForAutoconfiguration(Context::class)->addTag('fob.context');
    }

    public function process(ContainerBuilder $container): void
    {
        $this->provideBrowserKitIntegration($container);

        foreach ($container->findTaggedServiceIds('fob.context') as $serviceId => $attributes) {
            $serviceDefinition = $container->findDefinition($serviceId);

            $serviceDefinition->setPublic(true);
            $serviceDefinition->clearTag('fob.context');
        }
    }

    private function registerBehatContainer(ContainerBuilder $container): void
    {
        $behatServiceContainerDefinition = new Definition(ContainerInterface::class);
        $behatServiceContainerDefinition->setPublic(true);
        $behatServiceContainerDefinition->setSynthetic(true);

        $container->setDefinition('behat.service_container', $behatServiceContainerDefinition);
    }

    private function registerDriverBehatContainer(ContainerBuilder $container): void
    {
        $driverKernelDefinition = new Definition(KernelInterface::class, [SymfonyExtension::DRIVER_KERNEL_ID]);
        $driverKernelDefinition->setFactory([new Reference('behat.service_container'), 'get']);
        $driverKernelDefinition->setPublic(true);
        $driverKernelDefinition->setLazy(true);

        $driverServiceContainerDefinition = new Definition(ContainerInterface::class);
        $driverServiceContainerDefinition->setFactory([$driverKernelDefinition, 'getContainer']);
        $driverServiceContainerDefinition->setPublic(true);
        $driverServiceContainerDefinition->setLazy(true);

        $driverTestServiceContainerDefinition = new Definition(ContainerInterface::class, ['test.service_container']);
        $driverTestServiceContainerDefinition->setFactory([$driverServiceContainerDefinition, 'get']);
        $driverTestServiceContainerDefinition->setPublic(true);
        $driverTestServiceContainerDefinition->setLazy(true);

        $container->setDefinition('behat.driver.service_container', $driverTestServiceContainerDefinition);
        $container->registerAliasForArgument('behat.driver.service_container', ContainerInterface::class, 'driver container');
    }

    private function provideBrowserKitIntegration(ContainerBuilder $container): void
    {
        if (!$container->has('test.client')) {
            return;
        }

        if (class_exists(Client::class)) {
            $container->setAlias(Client::class, 'test.client');
        }

        $container->setAlias(KernelBrowser::class, 'test.client');
        $container->setAlias(HttpKernelBrowser::class, 'test.client');
    }

    private function provideMinkIntegration(ContainerBuilder $container): void
    {
        if (!class_exists(Mink::class)) {
            return;
        }

        $this->registerMink($container);
        $this->registerMinkDefaultSession($container);
        $this->registerMinkParameters($container);
    }

    private function registerMink(ContainerBuilder $container): void
    {
        $minkDefinition = new Definition(Mink::class, ['fob_symfony.mink']);
        $minkDefinition->setPublic(true);
        $minkDefinition->setLazy(true);
        $minkDefinition->setFactory([new Reference('behat.service_container'), 'get']);

        $container->setDefinition('behat.mink', $minkDefinition);
        $container->setAlias(Mink::class, 'behat.mink');
    }

    private function registerMinkDefaultSession(ContainerBuilder $container): void
    {
        $minkDefaultSessionDefinition = new Definition(Session::class);
        $minkDefaultSessionDefinition->setPublic(true);
        $minkDefaultSessionDefinition->setLazy(true);
        $minkDefaultSessionDefinition->setFactory([new Reference('behat.mink'), 'getSession']);

        $container->setDefinition('behat.mink.default_session', $minkDefaultSessionDefinition);
        $container->setAlias(Session::class, 'behat.mink.default_session');
    }

    private function registerMinkParameters(ContainerBuilder $container): void
    {
        $minkParametersDefinition = new Definition(MinkParameters::class, ['fob_symfony.mink.parameters']);
        $minkParametersDefinition->setPublic(true);
        $minkParametersDefinition->setLazy(true);
        $minkParametersDefinition->setFactory([new Reference('behat.service_container'), 'get']);

        $container->setDefinition('behat.mink.parameters', $minkParametersDefinition);
        $container->setAlias(MinkParameters::class, 'behat.mink.parameters');
    }
}

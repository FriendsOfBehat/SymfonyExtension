<?php

declare(strict_types=1);

namespace FriendsOfBehat\SymfonyExtension\Bundle\DependencyInjection;

use Behat\Behat\Context\Context;
use Behat\Mink\Session;
use FriendsOfBehat\SymfonyExtension\Mink\MinkParameters;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

final class FriendsOfBehatSymfonyExtensionExtension extends Extension implements CompilerPassInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $this->provideMinkIntegration($container);
        $this->registerBehatContainer($container);

        $container->registerForAutoconfiguration(Context::class)->addTag('fob.context');
    }

    public function process(ContainerBuilder $container): void
    {
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

    private function provideMinkIntegration(ContainerBuilder $container): void
    {
        $this->registerMinkDefaultSession($container);
        $this->registerMinkParameters($container);
    }

    private function registerMinkDefaultSession(ContainerBuilder $container): void
    {
        $minkDefaultSessionDefinition = new Definition(Session::class, ['fob_symfony.mink.default_session']);
        $minkDefaultSessionDefinition->setPublic(true);
        $minkDefaultSessionDefinition->setLazy(true);
        $minkDefaultSessionDefinition->setFactory([new Reference('behat.service_container'), 'get']);

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

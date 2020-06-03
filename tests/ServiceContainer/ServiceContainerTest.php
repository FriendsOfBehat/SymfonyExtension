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

namespace Tests\ServiceContainer;

use Behat\Behat\Context\Context;
use FriendsOfBehat\SymfonyExtension\ServiceContainer\SymfonyExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class ServiceContainerTest extends TestCase
{
    public function testAutoConfigurationCannotBeDone(): void
    {
        $container = $this->createMock(ContainerBuilder::class);
        $container->expects(self::never())->method('registerForAutoconfiguration');
        $container->expects(self::never())->method('findTaggedServiceIds');

        $extension = new SymfonyExtension();
        $extension->load($container, [
            'bootstrap' => null,
            'kernel' => [
                'class' => 'Kernel',
                'path' => 'src/',
            ],
            'autoconfigure' => false,
            'step_autowiring' => false,
        ]);
    }

    public function testAutoConfigurationCanBeDone(): void
    {
    }
}

final class FooContext implements Context
{
}

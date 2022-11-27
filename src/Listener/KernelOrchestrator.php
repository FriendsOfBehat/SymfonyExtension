<?php

declare(strict_types=1);

namespace FriendsOfBehat\SymfonyExtension\Listener;

use Behat\Behat\EventDispatcher\Event\ExampleTested;
use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelInterface;

final class KernelOrchestrator implements EventSubscriberInterface
{
    /** @var KernelInterface */
    private $symfonyKernel;

    /** @var KernelInterface */
    private $driverKernel;

    /** @var ContainerInterface */
    private $behatContainer;

    public function __construct(KernelInterface $symfonyKernel, KernelInterface $driverKernel, ContainerInterface $behatContainer)
    {
        $this->symfonyKernel = $symfonyKernel;
        $this->driverKernel = $driverKernel;
        $this->behatContainer = $behatContainer;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ScenarioTested::BEFORE => ['setUp', 15],
            ExampleTested::BEFORE => ['setUp', 15],
            ScenarioTested::AFTER => ['tearDown', -15],
            ExampleTested::AFTER => ['tearDown', -15],
        ];
    }

    public function setUp(): void
    {
        /** @psalm-suppress InvalidArgument Psalm complains that ContainerInterface does not match object|null */
        $this->symfonyKernel->getContainer()->set('behat.service_container', $this->behatContainer);
    }

    public function tearDown(): void
    {
        $this->driverKernel->shutdown();

        /*
         * Reset both Kernel instances after a scenario has been run: The Kernel (and thus Container)
         * used in Behat to configure Contexts; and the Kernel used by the SymfonyDriver to which
         * requests are dispatched (through Mink).
         *
         * Since the "symfony" container is needed in a few other places (where and why exactly?) and
         * has to be in a booted/usable state most of the time, we do not shut it down here in tearDown()
         * and boot it in setUp().
         *
         * Instead, the definitions in \FriendsOfBehat\SymfonyExtension\ServiceContainer\SymfonyExtension
         * make sure both kernels are booted immediately after being created, and we also initiate the
         * re-boot() here right away.
         */
        $this->symfonyKernel->getContainer()->set('behat.service_container', null);
        $this->symfonyKernel->shutdown();
        $this->symfonyKernel->boot();

        $this->driverKernel->boot();
    }
}

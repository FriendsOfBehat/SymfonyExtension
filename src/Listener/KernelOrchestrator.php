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

    /** @var ContainerInterface */
    private $behatContainer;

    public function __construct(KernelInterface $symfonyKernel, ContainerInterface $behatContainer)
    {
        $this->symfonyKernel = $symfonyKernel;
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
        $this->symfonyKernel->getContainer()->set('behat.service_container', $this->behatContainer);
    }

    public function tearDown(): void
    {
        $this->symfonyKernel->getContainer()->set('behat.service_container', null);
        $this->symfonyKernel->shutdown();
        $this->symfonyKernel->boot();
    }
}

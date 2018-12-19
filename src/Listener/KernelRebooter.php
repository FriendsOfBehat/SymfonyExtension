<?php

declare(strict_types=1);

namespace FriendsOfBehat\SymfonyExtension\Listener;

use Behat\Behat\EventDispatcher\Event\ExampleTested;
use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelInterface;

final class KernelRebooter implements EventSubscriberInterface
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
            ScenarioTested::AFTER => ['rebootSymfonyKernel', -15],
            ExampleTested::AFTER => ['rebootSymfonyKernel', -15],
            ScenarioTested::BEFORE => ['transferBehatContainer', 15],
            ExampleTested::BEFORE => ['transferBehatContainer', 15],
        ];
    }

    public function transferBehatContainer(): void
    {
        $symfonyContainer = $this->symfonyKernel->getContainer();

        $symfonyContainer->set('behat.service_container', $this->behatContainer);
    }

    public function rebootSymfonyKernel(): void
    {
        $this->symfonyKernel->shutdown();
        $this->symfonyKernel->boot();
    }
}

<?php

declare(strict_types=1);

namespace FriendsOfBehat\SymfonyExtension\Listener;

use Behat\Behat\EventDispatcher\Event\ExampleTested;
use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use FriendsOfBehat\SymfonyExtension\Kernel\KernelManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Orchestrates kernel lifecycle around Behat scenarios.
 *
 * Ensures proper isolation between scenarios by:
 * - Setting up kernel state before each scenario
 * - Tearing down and rebooting kernels after each scenario
 */
final class KernelOrchestrator implements EventSubscriberInterface
{
    private KernelManager $kernelManager;

    public function __construct(KernelManager $kernelManager)
    {
        $this->kernelManager = $kernelManager;
    }

    #[\Override]
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
        $this->kernelManager->setUp();
    }

    public function tearDown(): void
    {
        $this->kernelManager->tearDown();
    }
}

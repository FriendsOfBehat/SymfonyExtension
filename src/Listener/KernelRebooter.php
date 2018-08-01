<?php

declare(strict_types=1);

namespace FriendsOfBehat\SymfonyExtension\Listener;

use Behat\Behat\EventDispatcher\Event\ExampleTested;
use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelInterface;

final class KernelRebooter implements EventSubscriberInterface
{
    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @param KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ScenarioTested::AFTER => ['rebootKernel', -15],
            ExampleTested::AFTER => ['rebootKernel', -15],
        ];
    }

    public function rebootKernel(): void
    {
        $this->kernel->shutdown();
        $this->kernel->boot();
    }
}

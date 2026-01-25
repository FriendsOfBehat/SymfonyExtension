<?php

declare(strict_types=1);

namespace FriendsOfBehat\SymfonyExtension\Kernel;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Manages the dual-kernel architecture for Behat-Symfony integration.
 *
 * Context Kernel: Used for autowiring context classes and providing services to tests.
 *                 Rebooted after each scenario to ensure isolation.
 *
 * Driver Kernel:  Used by Mink's SymfonyDriver to handle HTTP requests.
 *                 Lazy-loaded only when Mink is actually used.
 *                 Rebooted before each Mink request for realistic behavior.
 */
final class KernelManager
{
    private KernelInterface $contextKernel;
    private ?KernelInterface $driverKernel = null;

    /** @var callable(): KernelInterface */
    private $driverKernelFactory;

    private ?ContainerInterface $behatContainer = null;

    /**
     * @param callable(): KernelInterface $driverKernelFactory Factory to create driver kernel on demand
     */
    public function __construct(KernelInterface $contextKernel, callable $driverKernelFactory)
    {
        $this->contextKernel = $contextKernel;
        $this->driverKernelFactory = $driverKernelFactory;
    }

    public function getContextKernel(): KernelInterface
    {
        return $this->contextKernel;
    }

    /**
     * Get driver kernel, creating it lazily if needed.
     */
    public function getDriverKernel(): KernelInterface
    {
        if ($this->driverKernel === null) {
            $this->driverKernel = ($this->driverKernelFactory)();
            $this->driverKernel->boot();
        }

        return $this->driverKernel;
    }

    /**
     * Check if driver kernel has been requested (indicates Mink usage).
     */
    public function isDriverKernelActive(): bool
    {
        return $this->driverKernel !== null;
    }

    /**
     * Store reference to Behat's service container for cross-container access.
     */
    public function setBehatContainer(ContainerInterface $container): void
    {
        $this->behatContainer = $container;
    }

    public function getBehatContainer(): ?ContainerInterface
    {
        return $this->behatContainer;
    }

    /**
     * Called before each scenario to set up kernel state.
     */
    public function setUp(): void
    {
        if ($this->behatContainer !== null) {
            $this->contextKernel->getContainer()->set('behat.service_container', $this->behatContainer);
        }
    }

    /**
     * Called after each scenario to reset kernel state for isolation.
     */
    public function tearDown(): void
    {
        // Shutdown driver kernel if it was used
        if ($this->driverKernel !== null) {
            $this->driverKernel->shutdown();
        }

        // Reset context kernel
        $this->contextKernel->getContainer()->set('behat.service_container', null);
        $this->contextKernel->shutdown();
        $this->contextKernel->boot();

        // Reboot driver kernel if it was used
        if ($this->driverKernel !== null) {
            $this->driverKernel->boot();
        }
    }
}

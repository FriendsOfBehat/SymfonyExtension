<?php

declare(strict_types=1);

namespace FriendsOfBehat\SymfonyExtension\Driver;

use Behat\Mink\Driver\BrowserKitDriver;
use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\HttpKernel\KernelInterface;

final class SymfonyDriver extends BrowserKitDriver
{
    /** @var KernelInterface */
    private $kernel;

    /** @var string|null */
    private $baseUrl;

    public function __construct(KernelInterface $kernel, ?string $baseUrl)
    {
        $this->kernel = $kernel;
        $this->baseUrl = $baseUrl;

        if (!$this->kernel->getContainer()->has('test.client')) {
            throw new \RuntimeException(sprintf(
                'Kernel "%s" used by Behat with "%s" environment and debug %s does not have "test.client" service. ' . "\n" .
                'Please make sure the kernel is using "test" environment or have "framework.test" configuration option enabled.',
                get_class($this->kernel),
                $this->kernel->getEnvironment(),
                $this->kernel->isDebug() ? 'enabled' : 'disabled',
            ));
        }

        parent::__construct($this->createBrowser(), $this->baseUrl);
    }

    public function reset()
    {
        parent::reset();

        /*
         * When \Behat\Mink\Driver\DriverInterface::visit() is called on this driver here,
         * we ultimately end up in \Symfony\Bundle\FrameworkBundle\KernelBrowser::doRequest().
         * That method tracks state across multiple requests to detect whether it is necessary
         * to reboot the targeted-at kernel before performing the next request.
         *
         * We do not want this state to leak between Behat scenarios, and so this method here
         * seems to be a good place to reset driver state as well.
         *
         * Since there is no other way to reset the KernelBrowser, we create a new instance.
         *
         * This also makes sense for another reason: The $kernel instance is rebooted by the
         * KernelOrchestrator between Behat scenarios. So, every time we reset the driver
         * (which happens at least for the first request during a scenario) we want to make
         * sure we are using a KernelBrowser instance created in the currently active
         * kernel "state" ("epoch"? "generation"?)
         */

        parent::__construct($this->createBrowser(), $this->baseUrl);
    }

    private function createBrowser(): AbstractBrowser
    {
        /** @var object $testClient */
        $testClient = $this->kernel->getContainer()->get('test.client');

        if (!$testClient instanceof AbstractBrowser) {
            throw new \RuntimeException(sprintf(
                'Service "test.client" should be an instance of "%s", "%s" given.',
                AbstractBrowser::class,
                get_class($testClient),
            ));
        }

        return $testClient;
    }
}

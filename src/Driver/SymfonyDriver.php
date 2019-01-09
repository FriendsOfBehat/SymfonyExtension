<?php

declare(strict_types=1);

namespace FriendsOfBehat\SymfonyExtension\Driver;

use Behat\Mink\Driver\BrowserKitDriver;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\HttpKernel\KernelInterface;

final class SymfonyDriver extends BrowserKitDriver
{
    public function __construct(KernelInterface $kernel, string $baseUrl)
    {
        if (!$kernel->getContainer()->has('test.client')) {
            throw new \RuntimeException(sprintf(
                'Kernel "%s" used by Behat with "%s" environment and debug %s does not have "test.client" service. ' . "\n" .
                'Please make sure the kernel is using "test" environment or have "framework.test" configuration option enabled.',
                get_class($kernel),
                $kernel->getEnvironment(),
                $kernel->isDebug() ? 'enabled' : 'disabled'
            ));
        }

        $testClient = $kernel->getContainer()->get('test.client');

        if (!$testClient instanceof Client) {
            throw new \RuntimeException(sprintf(
                'Service "test.client" should be an instance of "%s", "%s" given.',
                Client::class,
                get_class($testClient)
            ));
        }

        parent::__construct($testClient, $baseUrl);
    }
}

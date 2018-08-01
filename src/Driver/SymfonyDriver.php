<?php

declare(strict_types=1);

namespace FriendsOfBehat\SymfonyExtension\Driver;

use Behat\Mink\Driver\BrowserKitDriver;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\HttpKernel\KernelInterface;

final class SymfonyDriver extends BrowserKitDriver
{
    /**
     * @param KernelInterface $kernel
     * @param string $baseUrl
     */
    public function __construct(KernelInterface $kernel, string $baseUrl)
    {
        $testClient = $kernel->getContainer()->get('test.client');

        if (!$testClient instanceof Client) {
            throw new \RuntimeException(sprintf(
                'Expected service "test.client" to be an instance of "%s", got "%s" instead.',
                Client::class,
                \is_object($testClient) ? \get_class($testClient) : \gettype($testClient)
            ));
        }

        parent::__construct($testClient, $baseUrl);
    }
}

<?php

declare(strict_types=1);

namespace FriendsOfBehat\SymfonyExtension\Driver;

use Behat\Mink\Driver\BrowserKitDriver;
use Symfony\Component\HttpKernel\KernelInterface;

final class SymfonyDriver extends BrowserKitDriver
{
    /**
     * @param KernelInterface $kernel
     * @param string $baseUrl
     */
    public function __construct(KernelInterface $kernel, string $baseUrl)
    {
        parent::__construct($kernel->getContainer()->get('test.client'), $baseUrl);
    }
}

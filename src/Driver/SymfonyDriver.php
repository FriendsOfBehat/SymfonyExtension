<?php

namespace FriendsOfBehat\SymfonyExtension\Driver;

use Behat\Mink\Driver\BrowserKitDriver;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @author Kamil Kokot <kamil@kokot.me>
 */
final class SymfonyDriver extends BrowserKitDriver
{
    /**
     * @param KernelInterface $kernel
     * @param string $baseUrl
     */
    public function __construct(KernelInterface $kernel, $baseUrl)
    {
        parent::__construct($kernel->getContainer()->get('test.client'), $baseUrl);
    }
}

<?php

/*
 * This file is part of the SymfonyExtension package.
 *
 * (c) Kamil Kokot <kamil@kokot.me>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FriendsOfBehat\SymfonyExtension\Driver;

use Behat\Mink\Driver\BrowserKitDriver;
use Symfony\Component\HttpKernel\KernelInterface;

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

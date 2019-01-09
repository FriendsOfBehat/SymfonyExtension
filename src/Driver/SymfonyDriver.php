<?php

declare(strict_types=1);

namespace FriendsOfBehat\SymfonyExtension\Driver;

use Behat\Mink\Driver\BrowserKitDriver;
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

        parent::__construct($kernel->getContainer()->get('test.client'), $baseUrl);
    }
}

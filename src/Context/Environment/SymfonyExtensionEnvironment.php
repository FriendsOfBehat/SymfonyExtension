<?php

declare(strict_types=1);

/*
 * This file is part of the SymfonyExtension package.
 *
 * (c) Kamil Kokot <kamil@kokot.me>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FriendsOfBehat\SymfonyExtension\Context\Environment;

use Behat\Behat\Context\Environment\ContextEnvironment;
use FriendsOfBehat\SymfonyExtension\Context\Environment\Handler\ContextServiceEnvironmentHandler;

/**
 * @see ContextServiceEnvironmentHandler
 */
interface SymfonyExtensionEnvironment extends ContextEnvironment
{
}

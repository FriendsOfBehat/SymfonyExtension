<?php

declare(strict_types=1);

namespace FriendsOfBehat\SymfonyExtension\Mink;

use Behat\Mink\Mink as BaseMink;

class Mink extends BaseMink
{
    /**
     * Very weird bug happens when Mink is exposed as a LAZY service in tested application.
     * In that case, the destructor might be called at any random time. If it is called
     * while we're in the middle of any Mink-related operation, it'll leave the used Mink session
     * in an invalid state. Therefore, not stopping all the sessions while destructing Mink
     * saves our sanity.
     */
    public function __destruct()
    {
        // Intentionally left empty
    }
}

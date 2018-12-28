<?php

declare(strict_types=1);

namespace FriendsOfBehat\SymfonyExtension\Mink;

/** @final */
class MinkParameters
{
    /** @var array */
    private $minkParameters;

    public function __construct(array $minkParameters)
    {
        $this->minkParameters = $minkParameters;
    }

    public function all(): array
    {
        return $this->minkParameters;
    }

    public function get(string $parameter)
    {
        return $this->minkParameters[$parameter] ?? null;
    }
}

<?php

declare(strict_types=1);

namespace Tests\Behat\Config;

use Behat\Config\ConfigInterface;

final class ArrayConfig implements ConfigInterface
{
    public function __construct(private readonly array $data)
    {
    }

    public function toArray(): array
    {
        return $this->data;
    }
}

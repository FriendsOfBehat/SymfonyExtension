<?php

declare(strict_types=1);

namespace FriendsOfBehat\SymfonyExtension\Mink;

use ReturnTypeWillChange;

/** @final */
class MinkParameters implements \Countable, \IteratorAggregate, \ArrayAccess
{
    /** @var array */
    private $minkParameters;

    public function __construct(array $minkParameters)
    {
        $this->minkParameters = $minkParameters;
    }

    #[\Override]
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->minkParameters);
    }

    #[\Override]
    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->minkParameters);
    }

    /**
     * @return mixed
     */
    #[\Override]
    #[ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->minkParameters[$offset] ?? null;
    }

    #[\Override]
    public function offsetSet($offset, $value): void
    {
        throw new \BadMethodCallException(sprintf('"%s" is immutable.', self::class));
    }

    #[\Override]
    public function offsetUnset($offset): void
    {
        throw new \BadMethodCallException(sprintf('"%s" is immutable.', self::class));
    }

    #[\Override]
    public function count(): int
    {
        return count($this->minkParameters);
    }
}

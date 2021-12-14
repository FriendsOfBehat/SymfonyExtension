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

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->minkParameters);
    }

    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->minkParameters);
    }

    /**
     * @return mixed
     */
    #[ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->minkParameters[$offset] ?? null;
    }

    public function offsetSet($offset, $value): void
    {
        throw new \BadMethodCallException(sprintf('"%s" is immutable.', self::class));
    }

    public function offsetUnset($offset): void
    {
        throw new \BadMethodCallException(sprintf('"%s" is immutable.', self::class));
    }

    public function count(): int
    {
        return count($this->minkParameters);
    }
}

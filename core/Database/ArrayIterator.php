<?php

namespace Core\Database;;

class ArrayIterator implements \ArrayAccess, \IteratorAggregate
{
    /** @var array */
    protected array $attributes = [];

    /** @var array */
    protected array $original = [];

    /**
     * Create a new instance.
     *
     * @param  array  $items
     * @return void
     */
    public function __construct($items = [])
    {
        $this->attributes = $this->original = $items;
    }

    /**
     * Get an iterator for the items.
     *
     * @return \ArrayIterator<TKey, TValue>
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->attributes);
    }

    /**
     * Get an item at a given offset.
     *
     * @param  TKey  $key
     * @return TValue
     */
    public function offsetGet($key): mixed
    {
        return $this->attributes[$key];
    }

    /**
     * Set the item at a given offset.
     *
     * @param  TKey|null  $key
     * @param  TValue  $value
     * @return void
     */
    public function offsetSet($key, $value): void
    {
        if (is_null($key)) {
            $this->attributes[] = $value;
        } else {
            $this->attributes[$key] = $value;
        }
    }

    /**
     * Unset the item at a given offset.
     *
     * @param  TKey  $key
     * @return void
     */
    public function offsetUnset($key): void
    {
        unset($this->attributes[$key]);
    }

    /**
     * Determine if an item exists at an offset.
     *
     * @param  TKey  $key
     * @return bool
     */
    public function offsetExists($key): bool
    {
        return isset($this->attributes[$key]);
    }

    public function __get($name)
    {
        if (!isset($this->attributes[$name])) {
            throw new \Exception("Undefined property: " . static::class . "::$name");
        }

        return $this->offsetGet($name);
    }
}

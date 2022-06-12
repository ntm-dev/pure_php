<?php

namespace Core\Support\Helper;

class ArrayIterator implements \ArrayAccess, \IteratorAggregate
{
    private array $items = [];

    /**
     * Create a new instance.
     *
     * @param  array  $items
     * @return void
     */
    public function __construct($items = [])
    {
        $this->items = $items;
        foreach ($this->items as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Get an iterator for the items.
     *
     * @return \ArrayIterator<TKey, TValue>
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->items);
    }

    /**
     * Get an item at a given offset.
     *
     * @param  TKey  $key
     * @return TValue
     */
    public function offsetGet($key): mixed
    {
        return $this->items[$key];
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
            $this->items[] = $value;
        } else {
            $this->items[$key] = $value;
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
        unset($this->items[$key]);
    }

    /**
     * Determine if an item exists at an offset.
     *
     * @param  TKey  $key
     * @return bool
     */
    public function offsetExists($key): bool
    {
        return isset($this->items[$key]);
    }

    public function __get($name)
    {
        if (!isset($this->items[$name])) {
            throw new \Exception("Undefined property: " . static::class . "::$name");
        }

        return $this->offsetGet($name);
    }
}

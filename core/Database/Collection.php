<?php

namespace Core\Database;

use Traversable;
use ArrayAccess;
use JsonSerializable;
use IteratorAggregate;
use Core\Database\ArrayIterator;

/**
 * Collection helper.
 *
 * @template TKey of array-key
 * @template TValue
 *
 * @method \ArrayIterator append(mixed $value): void
 * @method \ArrayIterator asort(int $flags = SORT_REGULAR): bool
 * @method \ArrayIterator count(): int
 * @method \ArrayIterator current(): mixed
 * @method \ArrayIterator getArrayCopy(): array
 * @method \ArrayIterator getFlags(): int
 * @method \ArrayIterator key(): string|int|null
 * @method \ArrayIterator ksort(int $flags = SORT_REGULAR): bool
 * @method \ArrayIterator natcasesort(): bool
 * @method \ArrayIterator natsort(): bool
 * @method \ArrayIterator next(): void
 * @method \ArrayIterator offsetExists(mixed $key): bool
 * @method \ArrayIterator offsetGet(mixed $key): mixed
 * @method \ArrayIterator offsetSet(mixed $key, mixed $value): void
 * @method \ArrayIterator offsetUnset(mixed $key): void
 * @method \ArrayIterator rewind(): void
 * @method \ArrayIterator seek(int $offset): void
 * @method \ArrayIterator serialize(): string
 * @method \ArrayIterator setFlags(int $flags): void
 * @method \ArrayIterator uasort(callable $callback): bool
 * @method \ArrayIterator uksort(callable $callback): bool
 * @method \ArrayIterator unserialize(string $data): void
 * @method \ArrayIterator valid(): bool
 *
 * @see \ArrayIterator
 *
 * @author Nguyen The Manh <nguyenthemanh26011996@gmail.com>
 */
class Collection implements ArrayAccess, IteratorAggregate
{
    /**
     * The attributes contained in the collection.
     *
     * @var \ArrayIterator
     */
    protected $attributes = [];

    /**
     * Create a new collection.
     *
     * @param array  $attributes
     * @return void
     */
    public function __construct($attributes = [])
    {
        $this->attributes = $this->newArrayIterator($attributes);
    }

    private function newArrayIterator(array $attributes = [])
    {
        foreach ($attributes as &$value) {
            if ($this->isArrayable($value)) {
                $value = $this->newArrayIterator($value);
            }
        }

        return new ArrayIterator($this->getArrayableItems($attributes));
    }

    /**
     * Get all of the attributes in the collection.
     *
     * @return array<TKey, TValue>
     */
    public function all()
    {
        return iterator_to_array($this->attributes);
    }

    /**
     * Get the average value of a given key.
     *
     * @return float|int|null
     */
    public function avg()
    {
        $items = $this->map(function ($value) {
            return ($value);
        });
        if ($count = $items->count()) {
            return $items->sum() / $count;
        }
    }

    /**
     * Get the sum of the given values.
     *
     * @return mixed
     */
    public function sum()
    {
        $result = 0;
        $this->map(function ($value) use (&$result) {
            if ($value instanceof self) {
                return $value->sum();
            }
            // if ($this->isAllowedInitializationParameters($value)) {
            //     return $result += (new self($value))->sum();
            // }
            return $result += $value;
        });

        return $result;
    }

    /**
     * Run a map over each of the items.
     *
     * @template TMapValue
     *
     * @param  callable(TValue, TKey): TMapValue  $callback
     * @return static<TKey, TMapValue>
     */
    public function map(callable $callback)
    {
        $items = $this->all();
        $keys = array_keys($items);

        $items = array_map($callback, $items, $keys);

        return new static(array_combine($keys, $items));
    }

    public function pop()
    {
        $arrayItems = $this->all();
        $results = array_pop($arrayItems);
        $this->attributes = new ArrayIterator($arrayItems);

        return $results;
    }

    /**
     * Get an iterator for the items.
     *
     * @return \ArrayIteratorr<TKey, TValue>
     */
    public function getIterator()
    {
        return $this->attributes;
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

    /**
     * Results array of items from Collection or Arrayable.
     *
     * @param  mixed  $items
     * @return array
     */
    protected function getArrayableItems($items)
    {
        if (is_array($items)) {
            return $items;
        } elseif ($items instanceof JsonSerializable) {
            return (array) $items->jsonSerialize();
        } elseif ($items instanceof Traversable) {
            return iterator_to_array($items);
        }

        return (array) $items;
    }

    private function isArrayable($value)
    {
        return is_array($value) || $value instanceof JsonSerializable || $value instanceof Traversable;
    }

    private function isAllowedInitializationParameters($items)
    {
        return is_array($items) || ($items instanceof JsonSerializable) || ($items instanceof Traversable);
    }

    public function __get($name)
    {
        if (!isset($this->attributes[$name])) {
            throw new \Exception("Undefined property: " . static::class . "::$$name");
        }

        return $this->offsetGet($name);
    }

    public function __call($name, $arguments)
    {
        if (method_exists($this->attributes, $name)) {
            return call_user_func_array([$this->attributes, $name], $arguments);
        }
    }
}

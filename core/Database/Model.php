<?php

namespace Core\Database;

use Error;
use BadMethodCallException;
use Core\Database\Builder;
use Core\Database\ArrayIterator;

class Model extends ArrayIterator
{
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
    }

    public function getAttribute()
    {
        return $this->attributes;
    }

    public function setOriginal($original)
    {
        $this->original = $original;
    }

    public function getOriginal()
    {
        return $this->original;
    }

    public function save()
    {
        $id = $this->getBuilder()->insertGetId($this->getAttribute());

        return is_numeric($id) ? (int) $id : $id;
    }

    public function toArray()
    {
        return $this->getAttribute();
    }

    public function __set($name, $value)
    {
        $this->attributes[$name] = $value;
    }

    public function __toString()
    {
        return $this->atributes;
    }

    protected function getBuilder()
    {
        return new Builder($this);
    }

    protected function forwardCallTo($object, $method, $arguments)
    {
        try {
            return $object->{$method}(...$arguments);
        } catch (Error|BadMethodCallException $e) {
            throw new BadMethodCallException(sprintf(
                'Call to undefined method %s::%s()', static::class, $method
            ),
            $e->getCode(),
            $e
        );
        }
    }

    /**
     * Handle dynamic method calls into the model.
     *
     * @param  string  $method
     * @param  array  $arguments
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        return $this->forwardCallTo($this->getBuilder(), $method, $arguments);
    }

    /**
     * Handle dynamic static method calls into the model.
     *
     * @param  string  $method
     * @param  array  $arguments
     * @return mixed
     */
    public static function __callStatic($method, $arguments)
    {
        return (new static)->$method(...$arguments);
    }
}

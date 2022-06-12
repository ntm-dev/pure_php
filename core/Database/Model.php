<?php

namespace Core\Database;

use Core\Database\Builder;
use Core\Database\ArrayIterator;

class Model extends ArrayIterator
{
    /**
     * The current globally available.
     *
     * @var static
     */
    protected $instance;

    /** @var Builder */
    protected $builder;

    public function __construct()
    {
        $this->builder = new Builder($this);
        parent::__construct(...func_get_args());
    }

    /**
     * Get the globally available instance.
     *
     * @return static
     */
    public function getInstance()
    {
        return $this;
    }

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

    public function __toString()
    {
        return $this->atributes;
    }

    public static function __callStatic($method, $arguments)
    {
        $instance = new static;
        if (method_exists($instance->builder, $method)) {
            return $instance->builder->$method(...$arguments);
        }

        return $instance->$method(...$arguments);
    }
}

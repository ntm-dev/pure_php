<?php

namespace Core\Pattern;

trait Singleton
{
    /**
     * The current globally available.
     *
     * @var static
     */
    protected static $instance;

    /**
     * Get the globally available instance.
     *
     * @return static
     */
    public static function getInstance()
    {
        if (!static::$instance) {
            return static::$instance = new static(...func_get_args());
        }

        return static::$instance;
    }
}

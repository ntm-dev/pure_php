<?php

namespace Core\Pattern;

trait Singleton
{
    /**
     * The current globally available.
     *
     * @var static
     */
    private static $instance;

    /**
     * Get the globally available instance.
     *
     * @return static
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            return self::$instance = new self(...func_get_args());
        }

        return self::$instance;
    }

    /**
     * Set the shared instance of the container.
     *
     * @param  self|null  $container
     * @return static
     */
    public static function setInstance($instance = null)
    {
        return static::$instance = $instance;
    }
}

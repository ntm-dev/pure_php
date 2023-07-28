<?php

namespace Core\Support\Facades;

use RuntimeException;

abstract class Facade
{
    /**
     * The resolved object instances.
     *
     * @var array
     */
    protected static $resolvedInstance;

    /**
     * Get the registered name of the component.
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    protected static function getFacadeAccessor()
    {
        throw new RuntimeException('Facade does not implement getFacadeAccessor method.');
    }

    /**
     * Resolved instance.
     *
     * @param  array  $accessor
     * @param  bool   $refresh
     * @return object
     */
    protected static function resolvedInstance(string $accessor, $refresh = false)
    {
        if (!$refresh && isset(static::$resolvedInstance[$accessor])) {
            return static::$resolvedInstance[$accessor];
        }

        return static::$resolvedInstance[$accessor] = container($accessor);
    }

    /**
     * Get accessor instance.
     *
     * @param  bool   $refresh
     * @return object
     */
    public static function instance($refresh = false)
    {
        $instance = static::resolvedInstance(static::getFacadeAccessor(), $refresh);

        if (! $instance) {
            throw new RuntimeException('A facade root has not been set.');
        }

        return $instance;
    }

    /**
     * Handle dynamic, static calls to the object.
     *
     * @param  string  $method
     * @param  array  $arguments
     * @return mixed
     *
     * @throws \RuntimeException
     */
    public static function __callStatic($method, $arguments)
    {
        return self::instance()->$method(...$arguments);
    }
}

<?php

namespace App\Support\Trait;

trait CallStaticAble
{
    /** @var static */
    private static $instance;

    /**
     * Get instance.
     * 
     * @return static
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new static;
        }
        return self::$instance;
    }

    /**
     * Is triggered when invoking inaccessible methods in a static context.
     */
    public static function __callStatic($name, $arguments)
    {
        $instance = self::getInstance();
        if (in_array($name, get_class_methods($instance))) {
            return call_user_func_array([$instance, $name], $arguments);
        }
    }
}


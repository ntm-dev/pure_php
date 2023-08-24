<?php

namespace Core\Support\Traits;

use ReflectionMethod;
use BadMethodCallException;
use Core\Support\Helper\Str;
use Core\Pattern\Singleton;

/**
 * CallStaticAble trait.
 *
 * @author Nguyen The Manh <nguyenthemanh26011996@gmail.com>
 */
trait CallStaticAble
{
    use Singleton;

    /**
     * Is triggered when invoking inaccessible methods in a static context.
     */
    public static function __callStatic($name, $arguments)
    {
        $instance = self::getInstance();
        if (method_exists($instance, $name) && (new ReflectionMethod($instance, $name))->isPublic()) {
            return call_user_func_array([$instance, $name], $arguments);
        }

        if (defined(static::class . "::ALIAS_METHOD_PREFIX")) {
            $aliasMethods = [];
            foreach (static::ALIAS_METHOD_PREFIX as $prefix) {
                $aliasMethods[] = Str::camel(ltrim($name, $prefix));
                $aliasMethods[] = Str::camel($prefix . ucfirst($name));
            }
            foreach ($aliasMethods as $aliasMethod) {
                if (
                    method_exists($instance, $aliasMethod)
                    && (new ReflectionMethod($instance, $aliasMethod))->isPublic()
                ) {
                    return call_user_func_array([$instance, $aliasMethod], $arguments);
                }
            }
        }

        throw new BadMethodCallException("Method " . static::class . "::$name does not exist or is not accessible");
    }
}

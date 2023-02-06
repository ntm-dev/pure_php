<?php

namespace Core;

use Core\Support\Helper\Arr;
use Core\Pattern\Singleton;
use Core\Support\Facades\Storage as FacadesStorage;

/**
 * Config class
 *
 * @method static mixed get(string $keys, mixed $default = null)
 * @author Nguyen The Manh <nguyenthemanh26011996@gmail.com>
 */
class Config
{
    use Singleton;

    /**
     * @var array
     */
    private $configs;

    public function __construct()
    {
        $this->configs = app()->getConfig();
    }

    /**
     * @param  string  $property
     * @param  mixed   $default
     *
     * @return mixed
     */
    public static function get($property, $default = null)
    {
        return self::getInstance()->getConfig($property, $default);
    }

    /**
     * @param  string  $property
     * @param  mixed   $default
     *
     * @return mixed
     */
    private function getConfig($property, $default = null)
    {
        if (!$this->configs) {
            $this->configs = app()->getConfig();
        }

        return Arr::get($this->configs, $property, $default);
    }

    /**
     * Is triggered when invoking inaccessible methods in a static context.
     */
    public static function __callStatic($method, $arguments)
    {
        return self::getInstance()->$method(...$arguments);
    }
}

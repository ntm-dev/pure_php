<?php

namespace Core\Cache;

use Core\Contract\Cache;

/**
 * Cache manager class.
 *
 * @author Nguyen The Manh <nguyenthemanh26011996@gmail.com>
 */
class CacheManager implements Cache
{
    /**
     * Cache prefix
     */
    protected string $prefix;

    /**
     * Create a new Cache store.
     *
     * @param  string  $prefix
     * @return void
     */
    public function __construct($prefix = '')
    {
        $this->setPrefix($prefix ?: config('cache.prefix'));
    }

    /**
     * Return instance from config
     *
     * @return \Core\Contract\Cache
     */
    private function driver()
    {
        switch (config('cache.default')) {
            case 'redis':
                return app()->makeSingleton(RedisStore::class);
            default:
                return app()->makeSingleton(RedisStore::class);
        }
    }

    public function get($key, $default = null)
    {
        return $this->driver()->get("{$this->prefix}{$key}", $default);
    }

    public function has($key)
    {
        return $this->driver()->has("{$this->prefix}{$key}");
    }

    public function pull($key, $default = null)
    {
        return $this->driver()->pull("{$this->prefix}{$key}");
    }

    public function put($key, $value, $decaySeconds = null)
    {
        return $this->driver()->put("{$this->prefix}{$key}", $value, $decaySeconds);
    }

    public function forget($key)
    {
        return $this->driver()->forget("{$this->prefix}{$key}");
    }

    /**
     * Get the cache key prefix.
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Set the cache key prefix.
     *
     * @param  string  $prefix
     * @return void
     */
    public function setPrefix($prefix)
    {
        $this->prefix = ! empty($prefix) ? $prefix.':' : '';
    }
}

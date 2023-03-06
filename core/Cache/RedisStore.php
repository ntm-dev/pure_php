<?php

namespace Core\Cache;

use Core\Contract\Cache;
use Core\Redis\Connecter;

/**
 * Redis store cache class.
 *
 * @author Nguyen The Manh <nguyenthemanh26011996@gmail.com>
 */
class RedisStore implements Cache
{
    private static $connection;

    /**
     * A string that should be prepended to keys.
     *
     * @var string
     */
    protected $prefix;

    private function connection()
    {
        if (self::$connection) {
            return self::$connection;
        }

        return self::$connection = (new Connecter)->connect();
    }

    public function get($key, $default = null)
    {
        $value = $this->connection()->get($key);

        return ! is_null($value) ? $this->unserialize($value) : $default;
    }

    public function has($key)
    {
        return $this->connection()->exists($key);
    }

    public function pull($key, $default = null)
    {
        return tap($this->get($key, $default), function () use ($key) {
            $this->forget($key);
        });
    }

    public function put($key, $value, $ttl = null)
    {
        if ($ttl) {
            return (bool) $this->connection()->setex(
                $this->prefix.$key, (int) max(1, $ttl), $this->serialize($value)
            );
        }

        return (bool) $this->connection()->set(
            $this->prefix.$key, $this->serialize($value)
        );
    }

    public function forget($key)
    {
        return (bool) $this->connection()->del($this->prefix.$key);
    }

    /**
     * Serialize the value.
     *
     * @param  mixed  $value
     * @return mixed
     */
    protected function serialize($value)
    {
        return is_numeric($value) && ! in_array($value, [INF, -INF]) && ! is_nan($value) ? $value : serialize($value);
    }

    /**
     * Unserialize the value.
     *
     * @param  mixed  $value
     * @return mixed
     */
    protected function unserialize($value)
    {
        return is_numeric($value) ? $value : unserialize($value);
    }
}

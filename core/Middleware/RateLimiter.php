<?php

namespace Core\Middleware;

use Redis;
use Core\Support\Facades\Request;

/**
 * Rate Limiter class.
 *
 * @author Nguyen The Manh <nguyenthemanh26011996@gmail.com>
 */
class RateLimiter
{
    const CACHE_PREFIX = 'REPITTE-X-RATE-LIMITED';

    private $redis;

    public function __construct()
    {
        $this->init();
    }

    private function init()
    {
        $this->redis = new Redis;
        $redisConfig = get_redis_config();
        $this->redis->connect($redisConfig['host'], $redisConfig['port']);

        if (!empty($redisConfig['auth'])) {
            $this->redis->auth($redisConfig['auth']);
        }

        if (!empty($redisConfig['index'])) {
            $this->redis->select($redisConfig['index']);
        }

        return true;
    }

    /**
     * Determine if the given key has been "accessed" too many times.
     *
     * @param  int  $maxAttempts
     * @param  float|int  $decaySeconds
     * @param  string  $url
     * @return bool
     */
    public function tooManyAttempts($maxAttempts, $decaySeconds, $url = '')
    {
        $key = self::CACHE_PREFIX;
        if ($url) {
            $key .= ":$url";
        }
        $key .= ":" . Request::ip();

        if (!$this->redis->exists($key)) {
            $this->redis->set($key, 1);
            $this->redis->expire($key, $decaySeconds);
        } else {
            $this->redis->INCR($key);
            if ($this->redis->get($key) > $maxAttempts) {
                return true;
            }
        }

        return false;
    }
}

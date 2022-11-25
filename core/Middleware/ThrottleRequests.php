<?php

namespace Core\Middleware;

use Core\Config;
use Core\Pattern\Singleton;
use Core\Middleware\RateLimiter;
use Core\Support\Facades\Request;
use Core\Support\Facades\Response;

class ThrottleRequests
{
    use Singleton;

    /** @var int */
    const DEFAULT_DECAY_SECONDS = 60;

    /**
     * The rate limiter instance.
     *
     * @var \Support\RateLimiter
     */
    protected $limiter;

    /**
     * Create a new request throttler.
     *
     * @param  Support\RateLimiter  $limiter
     * @return void
     */
    public function __construct()
    {
        $this->limiter = new RateLimiter;
    }

    /**
     * Handle an incoming request.
     *
     * @return void
     */
    public static function handle()
    {
        try {
            $configs = self::getInstance()->getConfig();

            self::handleForCurrentPage($configs);
            self::handleForAllPage($configs);
        } catch (\Exception $th) {}
    }

    /**
     * Handle an incoming request for all page.
     *
     * @param  array  $configs
     * @return void
     */
    private static function handleForAllPage($configs)
    {
        if (isset($configs['all'])) {
            if (self::tooManyAttempts($configs['all'][0], isset($configs['all'][1]) ? $configs['all'][1] : self::DEFAULT_DECAY_SECONDS)) {
                Response::abort(429);
            }
        }
    }

    /**
     * Handle an incoming request for current page.
     *
     * @param  array  $configs
     * @return void
     */
    private static function handleForCurrentPage($configs)
    {
        $path = Request::path();
        if (!array_key_exists($path, $configs)) {
            return;
        }

        if (self::tooManyAttempts($configs[$path][0], isset($configs[$path][1]) ? $configs[$path][1] : self::DEFAULT_DECAY_SECONDS, $path)) {
            Response::abort(429);
        }
    }

    private static function tooManyAttempts($maxAttempts, $decaySeconds, $url = '')
    {
        return self::getInstance()->limiter->tooManyAttempts($maxAttempts, $decaySeconds, $url);
    }

    /**
     * Get config throttle url
     *
     * @return array
     */
    public function getConfig()
    {
        return Config::get('throttle', []);
    }
}

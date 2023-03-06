<?php

namespace Core\Contract;

/**
 * Cache contract.
 *
 * @author Nguyen The Manh <nguyenthemanh26011996@gmail.com>
 */
interface Cache
{
    /**
     * Retrieve an item from the cache.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public function get($key, $default = null);

    /**
     * Determine if an item exists in the cache.
     *
     * @param  string  $key
     * @return bool
     */
    public function has($key);

    /**
     * Retrieve an item from the cache and then delete the item.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public function pull($key, $default = null);

    /**
     * Store an item in the cache for a given number of seconds.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @param  int     $seconds
     * @return bool
     */
    public function put($key, $value, $seconds = null);

    /**
     * Remove items from the cache.
     *
     * @param  string  $key
     * @return bool
     */
    public function forget($key);
}

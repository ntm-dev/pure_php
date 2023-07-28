<?php

namespace Core\Redis;

use Redis;
use Core\Support\Helper\Arr;

/**
 * Redis connecter.
 *
 * @author Nguyen The Manh <nguyenthemanh26011996@gmail.com>
 */
class Connecter
{
    public function __construct()
    {
        if (!extension_loaded('redis')) {
            throw new \RuntimeException("Redis extension is require.");
        }
    }

    public function connect(array $config = [], array $options = [])
    {
        if (empty($config)) {
            $config = config('database.redis.default');
        }

        return $this->createClient(array_merge(
            $config, $options, Arr::pull($config, 'options', [])
        ));
    }

    /**
     * Create the Redis client instance.
     *
     * @param  array  $config
     * @return \Redis
     *
     * @throws \LogicException
     */
    protected function createClient(array $config)
    {
        return tap(new Redis, function ($client) use ($config) {
            $client->connect($config['host'], $config['port']);

            if (! empty($config['password'])) {
                $client->auth($config['password']);
            }

            if (isset($config['database'])) {
                $client->select((int) $config['database']);
            }

            if (! empty($config['prefix'])) {
                $client->setOption(Redis::OPT_PREFIX, $config['prefix']);
            }

            if (! empty($config['read_timeout'])) {
                $client->setOption(Redis::OPT_READ_TIMEOUT, $config['read_timeout']);
            }

            if (! empty($config['scan'])) {
                $client->setOption(Redis::OPT_SCAN, $config['scan']);
            }

            if (! empty($config['name'])) {
                $client->client('SETNAME', $config['name']);
            }

            if (array_key_exists('serializer', $config)) {
                $client->setOption(Redis::OPT_SERIALIZER, $config['serializer']);
            }

            if (array_key_exists('compression', $config)) {
                $client->setOption(Redis::OPT_COMPRESSION, $config['compression']);
            }

            if (array_key_exists('compression_level', $config)) {
                $client->setOption(Redis::OPT_COMPRESSION_LEVEL, $config['compression_level']);
            }
        });
    }
}

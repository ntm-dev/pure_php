<?php

namespace Core\Redis;

use Exception;
use Redis;
use Support\Helper\Arr;
use Support\Queue\RedisQueue;

class Connecter
{
    private $redis;

    public function __construct()
    {
        if (!extension_loaded('redis')) {
            throw new \RuntimeException("Redis extension is require.");
        }
        $this->redis = new Redis();
    }

    public function connect(array $config, array $options)
    {
        $connector = function () use ($config, $options) {
            return $this->createClient(array_merge(
                $config, $options, Arr::pull($config, 'options', [])
            ));
        };

        return new RedisQueue($connector(), $connector, $config);
        $connection = new RedisQueue(
            $this->redis, $config['queue'],
            Arr::get($config, 'connection', $this->connection),
            Arr::get($config, 'retry_after', 60)
        );
        return
        // dd(get_class_methods($this->redis));
        $host = config('database.redis.host');
        $port = config('database.redis.port');
        $username = config('database.redis.username');
        $password = config('database.redis.password');
        $database = config('database.redis.database');
        $this->redis->connect(config('database.redis.host'), config('database.redis.port'));
        $this->redis->auth(config('database.redis.password'));
        // $this->redis->rawcommand("auth", config('database.redis.username'), config('database.redis.password'));
        $this->redis->select(config('database.redis.database', 0));
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
        return tap($this->redis, function ($client) use ($config) {
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

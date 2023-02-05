<?php

namespace Core\Queue;

use Core\Support\Helper\Uuid;
use Core\Support\Facades\Date;
use Core\Support\Helper\DateTime;
use InvalidArgumentException;
use UnexpectedValueException;

abstract class Queue
{
    /**
     * The IoC container instance.
     *
     * @var \Illuminate\Container\Container
     */
    protected $container;

    /**
     * The encrypter implementation.
     *
     * @var \Illuminate\Contracts\Encryption\Encrypter
     */
    protected $encrypter;

    /**
     * The connection name for the queue.
     *
     * @var string
     */
    protected $connectionName;

    /**
     * Create a payload string from the given job and data.
     *
     * @param  string  $job
     * @param  mixed   $data
     * @param  string  $queue
     * @return string
     *
     * @throws \Illuminate\Queue\InvalidPayloadException
     */
    protected function createPayload($job, $data = '', $queue = null)
    {
        $payload = json_encode($this->createPayloadArray($job, $data, $queue));

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new InvalidArgumentException(
                'Unable to JSON encode payload. Error code: '.json_last_error()
            );
        }

        return $payload;
    }

    /**
     * Create a payload array from the given job and data.
     *
     * @param  string|array|object  $job
     * @param  mixed                $data
     * @return array
     */
    protected function createPayloadArray($job, $data)
    {
        if (!is_object($job)) {
            throw new UnexpectedValueException(sprintf("Argument 1 passed to %s:%s must be an object, %s given", static::class, __FUNCTION__, gettype($job)));
        }

        if ($job instanceof \Closure) {
            throw new UnexpectedValueException(sprintf("Argument 1 passed to %s:%s cannot be a closure", static::class, __FUNCTION__));
        }

        if (!method_exists($job, 'handle')) {
            throw new UnexpectedValueException(sprintf("%s must contain the handle method.", get_class($job)));
        }

        return $this->createObjectPayload($job, $data);
    }

    /**
     * Create a payload for an object-based queue handler.
     *
     * @param  mixed  $job
     * @param  mixed  $data
     * @return array
     */
    protected function createObjectPayload($job, $data)
    {
        return [
            'job' => [
                'commandName' => get_class($job),
                'command'     => serialize(clone $job),
                'data'        => $data
            ],
            'attempts' => 0,
            'uuid'     => Uuid::v4(),
        ];
    }

    /**
     * Get the connection name for the queue.
     *
     * @return string
     */
    public function getConnectionName()
    {
        return $this->connectionName;
    }

    /**
     * Set the connection name for the queue.
     *
     * @param  string  $name
     * @return $this
     */
    public function setConnectionName($name)
    {
        $this->connectionName = $name;

        return $this;
    }

    /**
     * Get the "available at" UNIX timestamp.
     *
     * @param  \Support\Helper\DateTime|int  $delay
     * @return int
     */
    protected function availableAt($delay = 0)
    {
        return $delay instanceof DateTime
                            ? $delay->timestamp()
                            : Date::now()->addSeconds($delay)->timestamp();
    }

    /**
     * Get the current system time as a UNIX timestamp.
     *
     * @return int
     */
    protected function currentTime()
    {
        return Date::now()->timestamp();
    }
}


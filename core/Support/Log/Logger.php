<?php

namespace Core\Support\Log;

use Core\Config;
use Core\Support\Helper\Str;
use Core\Support\Facades\Storage;

/**
 * Support Logger.
 *
 * @author Nguyen The Manh <nguyenthemanh26011996@gmail.com>
 */
class Logger
{
    /** @var object */
    private $logChannel;

    /**
     * Create a new log writer instance.
     *
     * @param  string  $logName
     * @return void
     */
    public function __construct($logFile = '')
    {
        $this->initLogChannel();
    }

    public function initLogChannel()
    {
        $channel = Config::get('logging.default');

        if ('cloudwatch' == $channel) {
            $this->logChannel = new CloudWatchLogger;
        } else {
            $this->logChannel = new LocalLogger;
        }
    }

    /**
     * Log an emergency message to the logs.
     *
     * @param  string|array  $message
     * @return void
     */
    public function emergency($message)
    {
        $this->writeLog(__FUNCTION__, $message);
    }

    /**
     * Log an alert message to the logs.
     *
     * @param  string|array  $message
     * @return void
     */
    public function alert($message)
    {
        $this->writeLog(__FUNCTION__, $message);
    }

    /**
     * Log a critical message to the logs.
     *
     * @param  string|array  $message
     * @return void
     */
    public function critical($message)
    {
        $this->writeLog(__FUNCTION__, $message);
    }

    /**
     * Log an error message to the logs.
     *
     * @param  string|array  $message
     * @return void
     */
    public function error($message)
    {
        $this->writeLog(__FUNCTION__, $message);
    }

    /**
     * Log a warning message to the logs.
     *
     * @param  string|array  $message
     * @return void
     */
    public function warning($message)
    {
        $this->writeLog(__FUNCTION__, $message);
    }

    /**
     * Log a notice to the logs.
     *
     * @param  string|array  $message
     * @return void
     */
    public function notice($message)
    {
        $this->writeLog(__FUNCTION__, $message);
    }

    /**
     * Log an informational message to the logs.
     *
     * @param  string|array  $message
     * @return void
     */
    public function info($message)
    {
        $this->writeLog(__FUNCTION__, $message);
    }

    /**
     * Log a debug message to the logs.
     *
     * @param  string|array  $message
     * @return void
     */
    public function debug($message)
    {
        $this->writeLog(__FUNCTION__, $message);
    }

    /**
     * Log a message to the logs.
     *
     * @param  string  $level
     * @param  string|array  $message
     * @return void
     */
    public function log($level, $message)
    {
        $this->writeLog($level, $message);
    }

    /**
     * Dynamically pass log calls into the writer.
     *
     * @param  string  $level
     * @param  string|array  $message
     * @return void
     */
    public function write($level, $message)
    {
        $this->writeLog($level, $message);
    }

    /**
     * Write a message to the log.
     *
     * @param  string  $level
     * @param  string|array  $message
     * @return void
     */
    protected function writeLog($level, $message)
    {
        $this->logChannel->writeLog($level, $message);
    }

    /**
     * Get logging configuration.
     *
     * @param  string  $key
     * @return array|null
     */
    protected function getConfig($key = '')
    {
        if ($key) {
            return Config::get("logging.{$key}");
        }

        return Config::get("logging");
    }

    public function __call($method, $arguments)
    {
        return $this->logChannel->$method(...$arguments);
    }
}

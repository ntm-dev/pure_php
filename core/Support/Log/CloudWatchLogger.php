<?php

namespace Core\Support\Log;

use Support\Config;
use Services\CloudWatchService;

final class CloudWatchLogger implements LoggerInterface
{
    /** @var  string  */
    private $logName;

    /** @var \Services\CloudWatchService */
    private $driver;

    public function __construct()
    {
        $this->driver = new CloudWatchService;
    }

    public function writeLog($level, $message)
    {
        $this->driver->push($this->getLogName(), (array)$message);
    }

    public function name($filename = '')
    {
        return $this->logName = $filename;
    }

    private function getLogName()
    {
        return $this->logName ?: Config::get('logging.channels.cloudwatch.name');
    }
}

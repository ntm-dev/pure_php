<?php

namespace Core\Support\Log;

interface LoggerInterface
{

    /**
     * Write a message to the log.
     *
     * @param  string  $level
     * @param  string|array  $message
     * @return void
     */
    public function writeLog($level, $message);

    /**
     * Define and return filename.
     *
     * @param  string  $filename
     * @return string
     */
    public function name($filename = '');
}

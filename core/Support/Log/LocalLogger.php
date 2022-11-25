<?php

namespace Core\Support\Log;

use Support\Config;
use Support\Helper\Str;
use Support\Facades\Storage;

final class LocalLogger implements LoggerInterface
{
    const DEFAULT_FILE_NAME = 'app';

    /** @var string */
    private $logFile;

    /** @var \Core\File\LocalAdapter */
    private $fileSystem;

    /**
     * Create a new log writer instance.
     *
     * @param  string  $logName
     * @return void
     */
    public function __construct($logFile = '')
    {
        $this->logFile = $logFile ?: '';
        $this->fileSystem = Storage::disk('local');
    }

    public function writeLog($level, $message)
    {
        $this->fileSystem->append(
            $this->getFileName(),
            $this->formatMessage($level, $message)
        );
    }

    /**
     * Format the parameters for the logger.
     *
     * @param  string  $level
     * @param  string|array  $message
     * @return string
     */
    private function formatMessage($level, $message)
    {
        if (is_array($message)) {
            $message = var_export($message, true);
        }

        $now = (\DateTime::createFromFormat('U.u', microtime(true)));
        $now->setTimezone(new \DateTimeZone(date_default_timezone_get()));

        return sprintf("[%s] %s.%s: %s", $now->format("Y-m-d H:i:s.u"), getenv('ENV'), Str::upper($level), $message);
    }

    public function getFileName()
    {
        if (! $this->logFile) {
            $this->name();
        }

        return $this->logFile;
    }

    public function name($filename = '')
    {
        $dir = Config::get("logging.dir") . DIRECTORY_SEPARATOR;

        if ($filename) {
            return $this->logFile = "{$dir}{$filename}.log";
        }

        return $this->logFile = $dir . $this->createDefaultFileName();
    }

    private function createDefaultFileName()
    {
        $channel = Config::get("logging.default");

        if ($channel == 'single') {
            return Config::get("logging.channels.single.filename") . ".log";
        } elseif ($channel == 'daily') {
            $this->clearDailyExpiredFile();
            return Config::get("logging.channels.daily.filename") . date('Y-m-d') . ".log";
        }
    }

    private function clearDailyExpiredFile()
    {
        $days = Config::get("logging.channels.daily.days");
        $expiredDate = strtotime(date("Y-m-d") . " - {$days} days");

        $files = $this->fileSystem->allFiles(Config::get("logging.channels.daily.dir"), false, false);

        foreach ($files as $file) {
            $dateStringPart = $this->dateStringPart($file);
            if ($dateStringPart && strtotime($dateStringPart) < $expiredDate) {
                $this->fileSystem->delete(Config::get("logging.channels.daily.dir") . DIRECTORY_SEPARATOR . $file);
            }
        }
    }

    private function dateStringPart($filename)
    {
        $configFileName = Config::get("logging.channels.daily.filename");
        $date = Str::before(Str::after($filename, $configFileName), ".log");

        $isCorrect = Str::endsWith($filename, ".log")
            && Str::startsWith($filename, $configFileName)
            && false !== \DateTime::createFromFormat('Y-m-d', $date);

        return $isCorrect ? $date : false;
    }
}

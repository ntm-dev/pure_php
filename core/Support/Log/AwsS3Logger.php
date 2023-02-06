<?php

namespace Core\Support\Log;

use Core\Support\Facades\Storage;

final class AwsS3Logger extends LocalLogger
{
    /** @var \Support\File\AwsS3Adapter */
    protected $fileSystem;

    public function __construct($logFile = '')
    {
        $this->logFile = $logFile ?: '';
        $this->fileSystem = Storage::disk('s3');
        if (config("logging.default") == 'daily') {
            $this->clearDailyExpiredFile();
        }
    }
}

<?php

namespace Core;

use Throwable;
use Exception;

class ApplicationException extends Exception
{
    public function __construct(Throwable $error)
    {
        parent::__construct($error->getMessage(), $error->getCode(), $error);
        $this->handle($error);
    }

    private function handle($error)
    {
        echo $error;
    }
}

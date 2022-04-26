<?php

namespace Core\Support\Facades;

use Core\Http\Response as RootResponse;
use Core\Support\Facades\Facade;

class Response extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return array
     */
    protected static function getFacadeAccessor()
    {
        return ['Response' => RootResponse::class];
    }
}

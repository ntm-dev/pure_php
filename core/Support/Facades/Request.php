<?php

namespace Core\Support\Facades;

use Core\Support\Facades\Facade;

class Request extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \Core\Http\Request::class;
    }
}

<?php

namespace Core\Support\Facades;

use Core\Support\Facades\Facade;
use Core\Support\Http\Session as Accessor;

/**
 * Support Session Facades.
 *
 * @author Nguyen The Manh <nguyenthemanh26011996@gmail.com>
 */
class Session extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return array
     */
    protected static function getFacadeAccessor()
    {
        return Accessor::class;
    }
}

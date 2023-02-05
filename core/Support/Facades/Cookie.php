<?php

namespace Core\Support\Facades;

use Core\Support\Facades\Facade;
use Core\Support\Http\Cookie as Accessor;

/**
 * Support Cookie Facades.
 *
 * @author Nguyen The Manh <nguyenthemanh26011996@gmail.com>
 */
class Cookie extends Facade
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

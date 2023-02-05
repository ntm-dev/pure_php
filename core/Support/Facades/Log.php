<?php

namespace Core\Support\Facades;

use Core\Support\Log\Logger as Accessor;
use Core\Support\Facades\Facade;

/**
 * Support Log Facade.
 *
 * @author Nguyen The Manh <nguyenthemanh26011996@gmail.com>
 */
class Log extends Facade
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

<?php

namespace Core\Support\Facades;

use Core\Support\Facades\Facade;

/**
 * Support Cache Facade.
 *
 * @author Nguyen The Manh <nguyenthemanh26011996@gmail.com>
 */
class Cache extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return array
     */
    protected static function getFacadeAccessor()
    {
        return \Core\Cache\CacheManager::class;
    }
}

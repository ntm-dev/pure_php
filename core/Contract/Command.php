<?php

namespace Core\Contract;

use RuntimeException;

/**
 * Command abstract.
 *
 * @author Nguyen The Manh <nguyenthemanh26011996@gmail.com>
 */
abstract class Command
{
    public function __construct()
    {
        if ('cli' !== php_sapi_name()) {
            throw new RuntimeException(sprintf("%s only support for php cli!", static::class));
        }
    }
}
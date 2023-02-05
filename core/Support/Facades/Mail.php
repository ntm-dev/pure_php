<?php

namespace Core\Support\Facades;

use Core\Support\Helper\Mailer\Mail as Accessor;
use Core\Support\Facades\Facade;

/**
 * Support Mail Facade.
 *
 * @method static $this from(string $address, string $name, bool $auto = true) Set the From and FromName properties
 * @method static $this replyTo(string $address, string $name) Add a "Reply-To" address.
 * @author Nguyen The Manh <nguyenthemanh26011996@gmail.com>
 */
class Mail extends Facade
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

<?php

namespace Core\Routing;

class RoureNotFoundException extends \Exception
{
    public function __construct($requestUrl)
    {
        parent::__construct("Route \"$requestUrl\" is not defined.");
    }
}
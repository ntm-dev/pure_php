<?php

namespace Core\Routing;

class RoureNotFoundException extends \Exception
{
    public function __construct($requestUrl)
    {
        if (!config('APP_DEBUG')) {
            http_response_code(404);
            echo "<h1>404 Not found</h1>";
            die;
        }

        parent::__construct("Route \"$requestUrl\" is not defined.");
    }
}

<?php

namespace Core\Routing;

use App\Routing\RoureNotFoundException;

class Route
{
    private static $routes = [];

    public static function get($url, $callback)
    {
        self::$routes[$url] = $callback;
    }

    public static function dispatch()
    {
        if (!array_key_exists($_SERVER['REQUEST_URI'], self::$routes)) {
            throw new RoureNotFoundException($_SERVER['REQUEST_URI']);
        }
        self::resolveRoute();
    }

    private static function resolveRoute()
    {
        $callback = self::$routes[$_SERVER['REQUEST_URI']];
        if ($callback instanceof \Closure) {
            return $callback();
        }
        if (is_string($callback)) {
            self::handleController($callback);
        }
    }

    private static function handleController($callback)
    {
        list($controllerName, $method) = explode("@", $callback);

        $controllerPath = app_path() . "/Controllers/$controllerName.php";
        $controllerName = "App\\Controllers\\$controllerName";

        if (include "$controllerPath") {
            $controller = new $controllerName();
            if (!in_array($method, get_class_methods($controller))) {
                throw new \Exception("$method not found in $controllerName");
            }
            return call_user_func_array([$controller, $method], []);
        }

        throw new \BadFunctionCallException("$controllerName not found");
    }
}

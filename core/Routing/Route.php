<?php

namespace Core\Routing;

use Core\Routing\RoureNotFoundException;

class Route
{
    private static $routes = [];

    /**
     * Register a new GET route with the router.
     *
     * @param  string $uri
     * @param  array|string|callable|null $action
     * @return array
     */
    public static function get($uri, $action)
    {
        return self::addRoute(['GET', 'HEAD'], $uri, $action);
    }

    /**
     * Register a new POST route with the router.
     *
     * @param  string $uri
     * @param  array|string|callable|null  $action
     * @return array
     */
    public static function post($uri, $action = null)
    {
        return self::addRoute('POST', $uri, $action);
    }

    /**
     * Add a route to the underlying route.
     *
     * @param  array|string $methods
     * @param  string $uri
     * @param  array|string|callable|null $action
     * @return array
     */
    public static function addRoute($methods, $uri, $action)
    {
        if (is_array($methods)) {
            foreach ($methods as $method) {
                self::$routes[$method][$uri] = $action;
            }
        } elseif (is_string($methods)) {
            self::$routes[$methods][$uri] = $action;
        }

        return self::$routes;
    }

    public static function dispatch()
    {
        if (!array_key_exists($_SERVER['REQUEST_URI'], self::getRouteList())) {
            $publicLocation = public_path() . $_SERVER['REQUEST_URI'];
            if (file_exists($publicLocation)) {
                return readfile($publicLocation);
            }

            throw new RoureNotFoundException($_SERVER['REQUEST_URI']);
        }

        self::resolveRoute();
    }

    private static function getRouteList($requestMethod = '')
    {
        return self::$routes[$requestMethod ?: $_SERVER['REQUEST_METHOD']];
    }

    private static function getRoute($uri = '')
    {
        return self::$routes[self::getRequestMethod()][$uri ?: $_SERVER['REQUEST_URI']];
    }

    private static function getCurrentRoute()
    {
        return self::getRoute();
    }

    private static function getRequestMethod()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    private static function resolveRoute()
    {
        $callback = self::getCurrentRoute();
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

<?php

namespace Core\Routing;

use Throwable;
use Exception;
use ReflectionMethod;
use BadMethodCallException;
use Core\Container\Container;
use Core\Support\Facades\Request;
use Core\Http\Exception\NotFoundException as HttpNotFoundException;

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
        if (!array_key_exists(Request::getInstance()->getPathInfo(), self::getRequestMethodRouteList())) {
            $publicLocation = public_path() . Request::getInstance()->getPathInfo();
            if (file_exists($publicLocation)) {
                return readfile($publicLocation);
            }

            throw new HttpNotFoundException(Request::getInstance()->getPathInfo());
        }

        return self::resolveRoute();
    }

    private static function getRequestMethodRouteList($requestMethod = '')
    {
        return self::$routes[$requestMethod ?: Request::method()];
    }

    public static function getRouteList()
    {
        return self::$routes;
    }

    private static function getRoute($uri = '')
    {
        return self::$routes[self::getRequestMethod()][$uri ?: Request::getInstance()->getPathInfo()];
    }

    private static function getCurrentRoute()
    {
        return self::getRoute();
    }

    private static function getRequestMethod()
    {
        return Request::method();
    }

    private static function resolveRoute()
    {
        $route = self::getCurrentRoute();

        if ($route instanceof \Closure) {
            return $route;
        }

        if (is_string($route)) {
            list($controllerName, $action) = explode("@", $route);

            return [
                'controller' => "App\\Controllers\\$controllerName",
                'action'     => $action,
            ];
        }

        throw new \LogicException(sprintf("Can not find route [%s] or route is not vaild", Request::path()));
    }
}

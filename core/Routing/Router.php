<?php

namespace Support\Routing;

use Closure;
use UnexpectedValueException;
use Support\Helper\Str;
use Support\Facades\Request;
use Support\Traits\MacroAble;

class Router
{
    use MacroAble;

    const REGEX_PARAM = "/\{([^}]+)\}/";
    private $routes = [];

    private $groupStack = [];

    /**
     * Register a new GET route with the router.
     *
     * @param  string $uri
     * @param  array|string|callable|null $action
     * @return Route
     */
    public function get($uri, $action)
    {
        return $this->addRoute(['GET', 'HEAD'], $uri, $action);
    }

    /**
     * Register a new POST route with the router.
     *
     * @param  string $uri
     * @param  array|string|callable|null  $action
     * @return Route
     */
    public function post($uri, $action = null)
    {
        return $this->addRoute('POST', $uri, $action);
    }

    /**
     * Register a new PUT route with the router.
     *
     * @param  string $uri
     * @param  array|string|callable|null  $action
     * @return Route
     */
    public function put($uri, $action = null)
    {
        return $this->addRoute('PUT', $uri, $action);
    }

    /**
     * Register a new PATCH route with the router.
     *
     * @param  string $uri
     * @param  array|string|callable|null  $action
     * @return Route
     */
    public function patch($uri, $action = null)
    {
        return $this->addRoute('PATCH', $uri, $action);
    }

    /**
     * Register a new DELETE route with the router.
     *
     * @param  string $uri
     * @param  array|string|callable|null  $action
     * @return Route
     */
    public function delete($uri, $action = null)
    {
        return $this->addRoute('DELETE', $uri, $action);
    }

    /**
     * Create a route group with shared attributes.
     *
     * @param  string|array  $attributes
     * @param  \Closure      $routes
     * @return void
     */
    public function group($attributes, Closure $routes)
    {
        if (is_array($attributes)) {
            if (!isset($attributes['prefix'])) {
                throw new UnexpectedValueException('Argument #1 ($attributes) must content prefix');
            }
            $prefix = $attributes['prefix'];
        } elseif (is_string($attributes)) {
            $prefix = $attributes;
        } else {
            throw new UnexpectedValueException(
                sprintf(
                    'Argument #1 ($attributes) must be of type array or string, %s given',
                    gettype($attributes)
                )
            );
        }

        $groupStack = $this->groupStack;
        $this->groupStack[] = $prefix;
        $routes();
        $this->groupStack = $groupStack;
    }

    /**
     * Route a resource to a controller.
     *
     * @param  string  $name
     * @param  string  $controller
     *
     * @mixin \Support\Routing\Route::middleware
     * @return $this
     */
    public function resource($name, $controller)
    {
        $name = trim($name, '/');
        $newRoutes = [
            $this->get($name, "{$controller}@index")->name(Str::replace("/", ".", "{$name}.index")),
            $this->get("{$name}/create", "{$controller}@create")->name(Str::replace("/", ".", "{$name}.create")),
            $this->post($name, "{$controller}@store")->name(Str::replace("/", ".", "{$name}.store")),
            $this->get("{$name}/{param}", "{$controller}@show")->name(Str::replace("/", ".", "{$name}.show")),
            $this->get("{$name}/{param}/edit", "{$controller}@edit")->name(Str::replace("/", ".", "{$name}.edit")),
            $this->put("{$name}/{param}", "{$controller}@update")->name(Str::replace("/", ".", "{$name}.update")),
            $this->patch("{$name}/{param}", "{$controller}@update")->name(Str::replace("/", ".", "{$name}.update")),
            $this->delete("{$name}/{param}", "{$controller}@destroy")->name(Str::replace("/", ".", "{$name}.destroy")),
        ];

        $instance = clone $this;
        $instance->macro('middleware', function($middleware) use ($newRoutes) {
            foreach ($newRoutes as $route) {
                $route->middleware($middleware);
            }
        });

        return $instance;
    }

    /**
     * Add a route to the underlying route.
     *
     * @param  array|string $methods
     * @param  string $uri
     * @param  array|string|callable|null $action
     * @return Route
     */
    public function addRoute($methods, $uri, $action)
    {
        $uri = static::prefix($uri);
        $route = new Route($methods, $uri, $action);

        if (is_array($methods)) {
            foreach ($methods as $method) {
                $this->routes[$method][$uri] = $route;
            }
        } elseif (is_string($methods)) {
            $this->routes[$methods][$uri] = $route;
        }

        return $route;
    }

    /**
     * @todo hoan thanh no
     */
    public function getRoute($name)
    {
        $route = $this->findRoute($name);
        if ($route instanceof Route) {
            return $route;
        }

        throw new \LogicException(sprintf("Can not find route [%s]", $name));
    }

    /**
     * Find a route by name
     *
     * @param  string  $name
     * @return Route|false
     */
    public function find($name)
    {
        foreach ($this->routes as $routes) {
            foreach ($routes as $route) {
                if ($route->name === $name) {
                    return $route;
                }
            }
        }

        return false;
    }

    public function resolveRoute()
    {
        $route = $this->getCurrentRoute();

        if ($route instanceof Route) {
            $route->addParameter($this->getCurrentRouteParams());
            if (is_string($route->action)) {
                list($controllerName, $action) = explode("@", $route->action);
                $route->controller = $controllerName;
                $route->action = $action;
            }
            return $route;
        }

        throw new \LogicException(sprintf("Can not find route [%s] or route is not valid", Request::path()));
    }

    public function isExistsRoute($uri = '')
    {
        $uri = $uri ?: $this->getCurrentUri();
        $routers = $this->getRequestMethodRouteList();
        if (array_key_exists($uri, $routers)) {
            return true;
        }

        foreach (array_keys($routers) as $routeName) {
            if ($this->compareRoute($routeName, $uri)) {
                return true;
            }
        }

        return false;
    }

    public function getRouteList()
    {
        return $this->routes;
    }

    public function getCurrentUri()
    {
        return trim(Request::getInstance()->getPathInfo(), "/") ?: '/';
    }

    public function getCurrentRouteParams()
    {
        return $this->getUriParams(
            explode("/", $this->getCurrentUri()),
            explode("/", $this->getCurrentRouteName())
        );
    }

    private function getUriParams($segmentUri, $segmentRouteName)
    {
        $uriParams = [];
        foreach ($segmentUri as $key => $segment) {
            if ($segmentRouteName[$key] === $segment) {
                continue;
            }
            $uriParams[] = $segment;
        }

        return $uriParams;
    }

    /**
     * Concat prefix with uri.
     *
     * @param  string  $uri
     * @return string
     */
    private function prefix($uri)
    {
        return trim(trim(end($this->groupStack), '/').'/'.trim($uri, '/'), '/') ?: '/';
    }

    private function getRequestMethodRouteList($requestMethod = '')
    {
        return $this->routes[$requestMethod ?: Request::method()];
    }

    private function getCurrentRoute()
    {
        $routeName = $this->getCurrentRouteName();
        if (false === $routeName) {
            return false;
        }

        return $this->getRequestMethodRouteList()[$routeName];
    }

    private  function getCurrentRouteName()
    {
        $uri = $this->getCurrentUri();

        foreach (array_keys($this->getRequestMethodRouteList()) as $routeName) {
            if ($this->compareRoute($routeName, $uri)) {
                return $routeName;
            }
        }

        return false;
    }

    private function compareRoute($routeName, $uri)
    {
        $segmentUri = explode("/", $uri);
        $segmentRouteName = explode("/", $routeName);
        if (count($segmentUri) !== count($segmentRouteName)) {
            return false;
        }

        $routeParams = $this->getUriParams($segmentUri, $segmentRouteName);

        return $uri === preg_replace_callback(self::REGEX_PARAM, function () use (&$routeParams) {
            return array_shift($routeParams);
        }, $routeName);
    }
}

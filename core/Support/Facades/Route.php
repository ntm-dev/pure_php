<?php

namespace Core\Support\Facades;

use Core\Support\Facades\Facade;

/**
 * Support Router Facade.
 * @experimental
 *
 * @author Nguyen The Manh <nguyenthemanh26011996@gmail.com>
 *
 * @method static \Core\Routing\Route get(string $uri, array|string|callable|null $action) Register a new GET route with the router.
 * @method static \Core\Routing\Route post(string $uri, array|string|callable|null $action) Register a new POST route with the router.
 * @method static \Core\Routing\Route put(string $uri, array|string|callable|null $action) Register a new PUT route with the router.
 * @method static \Core\Routing\Route patch(string $uri, array|string|callable|null $action) Register a new PATCH route with the router.
 * @method static \Core\Routing\Route delete(string $uri, array|string|callable|null $action) Register a new DELETE route with the router.
 * @method static void group(string|array $attributes, \Closure $routes) Create a route group with shared attributes.
 * @method static \Core\Routing\Router resource(string $name, string $controller) Route a resource to a controller.
 * @method static \Core\Routing\Route addRoute(string $methods, string $uri, array|string|callable|null $action)  Determine if the given ability should be denied for the current user.
 * @method static \Core\Routing\Route|false find(string $name) Find a route by name.
 */
class Route extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \Core\Routing\Router::class;
    }
}

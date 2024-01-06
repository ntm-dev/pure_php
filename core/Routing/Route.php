<?php

namespace Core\Routing;

use Closure;
use Core\Support\Facades\Request;
use Core\Http\Exception\NotFoundException as HttpNotFoundException;
use Support\Validation\Validator;

class Route
{
    /**
     * The route name.
     */
    public string $name;

    /**
     * The URI pattern the route responds to.
     */
    public string $uri;

    /**
     * The HTTP methods the route responds to.
     */
    public array $methods = [];

    /**
     * The route action array.
     */
    public Closure|string $action;

    /**
     * The array of matched parameters.
     */
    public ?array $parameters = [];

    /**
     * The controller instance.
     */
    public mixed $controller;

    /**
     * The validators used by the routes.
     */
    public static ?array $validators;

    /**
     * The $middlewares used by the routes.
     */
    public ?array $middlewares = [];

    /**
     * Create a new Route instance.
     *
     * @return void
     */
    public function __construct(array|string $methods, string $uri, Closure|string $action)
    {
        $this->uri = $uri;
        $this->methods = (array) $methods;
        $this->action = $action;

        if (in_array('GET', $this->methods) && ! in_array('HEAD', $this->methods)) {
            $this->methods[] = 'HEAD';
        }
    }

    /**
     * Run the route action and return the response.
     *
     * @return mixed
     */
    public function run()
    {
        if ($this->action instanceof Closure) {
            $closure = $this->action;
            return $closure();
        }
        /** @var \App\Controllers\BaseController */
        $controller = container($this->controller);
        if ($this->middlewares) {
            foreach ($this->middlewares as $middleware) {
                $controller->middleware($middleware);
            }
        }

        $dependencies = container()->resolveClassMethodDependencies(
            $controller,
            $this->action,
            true
        );

        foreach ($dependencies as $key => $dependency) {
            if ($dependency instanceof Validator) {
                if (!$dependency->validate()) {
                    return response()->json($dependency->errors())->setStatusCode(422);
                }
            }
        }

        return $controller->{$this->action}(...(array_merge(
            $dependencies,
            $this->parameters ?: []
        )));
    }

    /**
     * Determine if the route has parameters.
     *
     * @param  mixed  $parameter
     * @return $this
     */
    public function addParameter($parameter)
    {
        $this->parameters = array_merge((array) $this->parameters, (array) $parameter);

        return $this;
    }

    /**
     * Get the key / value list of parameters for the route.
     *
     * @return array|null
     */
    public function parameters()
    {
        return $this->parameters;
    }

    /**
     * Add or change the route name.
     *
     * @param  string  $name
     * @return $this
     */
    public function name($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get or set the middlewares attached to the route.
     *
     * @param  array|string|null  $middleware
     * @return $this|array
     */
    public function middleware($middleware = null)
    {
        if (is_null($middleware)) {
            return [];
        }

        if (! is_array($middleware)) {
            $middleware = func_get_args();
        }

        $this->middlewares = array_merge(
            (array) ($this->middlewares),
            $middleware
        );

        return $this;
    }

    /**
     * Generate the URL to a named route.
     *
     * @param  array  $parameters
     * @return string
     */
    public function generateUrl(array $parameters)
    {
        $parameters = !empty($parameters) ? $parameters : $this->parameters();

        return Request::root() . "/" . preg_replace_callback(
            "/\{([^}]+)\}/",
            function () use (&$parameters) {
                return array_shift($parameters);
            },
            $this->uri
        );
    }
}

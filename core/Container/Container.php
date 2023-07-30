<?php

namespace Core\Container;

use Closure;
use Exception;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;
use Core\Pattern\Singleton;

class Container
{
    use Singleton;

    /**
     * The container's shared instances.
     *
     * @var array[]
     */
    protected $instances = [];

    /**
     * The container's bindings.
     *
     * @var array
     */
    protected $bindings = [];

    /**
     * The stack of concretions currently being built.
     *
     * @var array[]
     */
    protected $buildStack = [];

    /**
     * Register a class or interface to Container
     *
     * @param  string|object $abstract
     * @param  object|null   $concrete
     * @param  bool          $shared
     */
    public function bind($abstract, $concrete = null, $shared = false)
    {
        $abstract = $this->normalize($abstract);

        $concrete = $this->normalize($concrete);

        if (is_null($concrete)) {
            $concrete = $abstract;
        }

        if (is_object($concrete)) {
            $this->instances[$abstract] = $concrete;
        } elseif (! $concrete instanceof Closure) {
            if (! is_string($concrete)) {
                throw new \UnexpectedValueException(self::class.'::bind(): Argument #2 ($concrete) must be of type Closure|string|null');
            }
        }

        $this->bindings[$abstract] = compact('concrete', 'shared');
    }

    /**
     * Determine if the given abstract type has been bound.
     *
     * @param  string  $abstract  bnm,sdferty  df`      2
     * @return bool
     */
    public function bound($abstract)
    {
        $abstract = $this->normalize($abstract);

        return isset($this->bindings[$abstract]) || isset($this->instances[$abstract]);
    }

    /**
     * Register a shared binding in the container and make it.
     *
     * @param  string|array          $abstract
     * @param  \Closure|string|null  $concrete
     * @return void
     */
    public function makeSingleton($abstract, $concrete = null)
    {
        $this->bind($abstract, $concrete, true);

        return $this->make($abstract);
    }

    /**
     * Register a shared binding in the container.
     *
     * @param  string|array          $abstract
     * @param  \Closure|string|null  $concrete
     * @return void
     */
    public function singleton($abstract, $concrete = null)
    {
        $this->bind($abstract, $concrete, true);
    }

    /**
     * Determine if a given type is shared.
     *
     * @param  string  $abstract
     * @return bool
     */
    public function isShared($abstract)
    {
        return isset($this->instances[$abstract]) ||
              (isset($this->bindings[$abstract]['shared']) &&
               $this->bindings[$abstract]['shared'] === true);
    }

    /**
     * Get instance from Container
     *
     * @param $abstract
     * @return mixed|object
     * @throws Exception
     */
    public function make($abstract)
    {
        if (! $this->bound($abstract)) {
            $this->bind($abstract);
        }

        if ($this->isShared($abstract)) {
            return $this->isResolved($abstract)
                ? $this->getResolvedInstance($abstract)
                : $this->instances[$abstract] = $this->build($abstract);
        }

        return  $this->build($abstract);
    }

    public function isResolved($abstract)
    {
        return isset($this->instances[$abstract]);
    }

    /**
     * Get resolved instance
     *
     * @param  string  $abstract
     * @return object|array|null
     */
    public function getResolvedInstance($abstract = '')
    {
        if ($abstract) {
            return isset($this->instances[$abstract]) ? $this->instances[$abstract] : null;
        }

        return $this->instances;
    }

    /**
     * Instantiate a concrete instance of the given type.
     *
     * @param  string  $concrete
     * @return mixed
     *
     * @throws \Exception
     */
    public function build($concrete)
    {
        try {
            $reflector = new ReflectionClass($concrete);
        } catch (ReflectionException $e) {
            throw new Exception("Target class [$concrete] does not exist.", 0, $e);
        }

        // If the type is not instantiable, the developer is attempting to resolve
        // an abstract type such as an Interface or Abstract Class and there is
        // no binding registered for the abstractions so we need to bail out.
        if (! $reflector->isInstantiable()) {
            return $this->notInstantiable($concrete);
        }

        $this->buildStack[] = $concrete;

        $constructor = $reflector->getConstructor();

        // If there are no constructors, that means there are no dependencies then
        // we can just resolve the instances of the objects right away, without
        // resolving any other types or dependencies out of these containers.
        if (is_null($constructor)) {
            array_pop($this->buildStack);

            return new $concrete;
        }

        $dependencies = $constructor->getParameters();

        // Once we have all the constructor's parameters we can create each of the
        // dependency instances and then use the reflection instances to make a
        // new instance of this class, injecting the created dependencies in.
        try {
            $instances = $this->resolveDependencies($dependencies);
        } catch (Exception $e) {
            array_pop($this->buildStack);

            throw $e;
        }

        array_pop($this->buildStack);

        return $reflector->newInstanceArgs($instances);
    }

    /**
     * Resolve dependencies for an class method.
     *
     * @param  \stdClass  $instance
     * @param  string     $method
     */
    public function resolveClassMethodDependencies($instance, $method)
    {
        return $this->resolveDependencies(
            (new \ReflectionMethod($instance, $method))->getParameters()
        );
    }

    /**
     * Resolve all of the dependencies from the ReflectionParameters.
     *
     * @param  \ReflectionParameter[]  $dependencies
     * @return array
     *
     * @throws \Exception
     */
    protected function resolveDependencies(array $dependencies)
    {
        $results = [];

        foreach ($dependencies as $dependency) {
            if ($dependency->getType() && !$dependency->getType()->isBuiltin()) { // check if dependency is a class
                // new ReflectionClass($dependency->getType()->getName())
                $results[] = $this->make($dependency->getType()->getName());
            } else {
                if ($dependency->isDefaultValueAvailable()) {
                    $results[] = $dependency->getDefaultValue();
                } else {
                    throw new Exception("Can not resolve dependency {$dependency->name}");
                }
            }
        }

        return $results;
    }

    /**
     * Throw an exception that the concrete is not instantiable.
     *
     * @param  string  $concrete
     * @return void
     *
     * @throws \Exception
     */
    protected function notInstantiable($concrete)
    {
        if (! empty($this->buildStack)) {
            $previous = implode(', ', $this->buildStack);

            $message = "Target [$concrete] is not instantiable while building [$previous].";
        } else {
            $message = "Target [$concrete] is not instantiable.";
        }

        throw new Exception($message);
    }

    /**
     * Normalize the given class name by removing leading slashes.
     *
     * @param  mixed  $service
     * @return mixed
     */
    protected function normalize($service)
    {
        return is_string($service) ? ltrim($service, '\\') : $service;
    }

}

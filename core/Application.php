<?php

namespace Core;

use Dotenv\Dotenv;
use RuntimeException;
use Core\Routing\Route;
use Core\Contract\Provider;
use Core\Support\Helper\Str;
use Core\Container\Container;
use Spatie\Ignition\Ignition;
use Core\Support\Facades\Response;
use Core\Http\Exception\HttpException;

class Application extends Container
{
    /**
     * The registered config.
     *
     * @var array
     */
    protected $configs = [];

    /**
     * The registered type aliases.
     *
     * @var array
     */
    protected $aliases = [];

    /**
     * The registered type router.
     *
     * @var array
     */
    protected $routes = [];

    /**
     * The registered type router.
     *
     * @var array
     */
    protected $request;

    public function __construct()
    {
        if (!self::$instance) {
            $this->bootstrap();
        }
    }

    /**
     * Bootstrapt application
     *
     * @return void
     */
    private function bootstrap()
    {
        $this->loadConfig();
        $this->loadAlias();
        $this->loadProvider();
        $this->loadRoutes();
    }

    /**
     * Load configs.
     *
     * @return void
     */
    private function loadConfig()
    {
        Dotenv::createUnsafeImmutable(base_path())->load();

        $configs = [];
        $files = @array_diff(@scandir(base_path('config')), array('.', '..')) ?: [];

        foreach ($files as $file) {
            $configs += [Str::beforeLast($file, ".") => @include base_path("config/$file")];
        }

        $this->configs = array_merge($configs, $_ENV);
    }

    /**
     * Get configs.
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->configs;
    }

    /**
     * Load aliases.
     *
     * @return void
     */
    private function loadAlias()
    {
        $this->aliases = $this->configs['app']['aliases'];
        foreach ($this->aliases as $alias => $class) {
            class_alias($class, $alias);
        }
    }

    private function loadProvider()
    {
        $loadedProviders = [];

        foreach ($this->configs['app']['providers'] as $provider) {
            $provider = $this->make($provider);
            if (!$provider instanceof Provider) {
                throw new RuntimeException(sprintf("%s must be instance of %s", get_class($provider), Provider::class));
            }
            $loadedProviders[] = $provider;
            $provider->setApplicationContainer($this);
            $provider->register();
        }

        foreach ($loadedProviders as $provider) {
            $provider->boot();
        }
    }

    /**
     * Load routes.
     *
     * @return void
     */
    private function loadRoutes()
    {
        require base_path() . "/routes/web.php";

        $this->routes = Route::getRouteList();
    }

    /**
     * Dispatch application.
     *
     * @return mixed;
     */
    public function dispatch()
    {
        $this->registerShutdownFunction();

        try {
            $route = Route::dispatch();
        } catch (\Throwable $th) {
            if (! $th instanceof HttpException) {
                throw $th;
            }
            $response = $th->response();
        }

        if ($route instanceof \Closure) {
            $response = $route();
        } else {
            $controller = $this->getController($route['controller']);
            $response = $controller->{$route['action']} (
                ...$this->resolveClassMethodDependencies($controller, $route['action'])
            );
        }

        Response::setContent($response)->send();
    }

    /**
     * Get the controller instance for the route.
     *
     * @return mixed
     */
    public function getController($name)
    {
        try {
            return $this->make($name);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Register shutdown function.
     *
     * @return mixed
     */
    private function registerShutdownFunction()
    {
        Ignition::make()->register()->configureFlare(function($flare) {
            $flare->registerMiddleware([
                new class implements \Spatie\FlareClient\FlareMiddleware\FlareMiddleware
                {
                    public function handle(\Spatie\FlareClient\Report $report, \Closure $next)
                    {
                        $report->frameworkVersion(env("APP_VERSION", '1.0.0'));
                        $report->group('env', [
                            'app_version' => env("APP_VERSION", '1.0.0'),
                            'app_locale' => env("APP_LOCALE", 'en'),
                            'app_config_cached' => false,
                            'app_debug' => config('app.debug', true),
                            'app_env' => config('app.env', 'local'),
                            'php_version' => phpversion(),
                        ]);

                        return $next($report);
                    }
                }
            ]);
        });
    }
}

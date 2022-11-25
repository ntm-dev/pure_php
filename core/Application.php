<?php

namespace Core;

use Dotenv\Dotenv;
use Core\Http\Request;
use Core\Routing\Route;
use Core\Pattern\Singleton;
use Core\Container\Container;
use Spatie\Ignition\Ignition;
use Core\Support\Facades\Storage;
use Core\Support\Facades\Response;
use Core\Http\Exception\HttpException;

class Application extends Container
{
    /**
     * The registered type aliases.
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
        $this->bootstrap();
    }

    /**
     * Bootstrapt application
     *
     * @return void
     */
    private function bootstrap()
    {
        ini_set('display_errors', 0);
        $this->loadConfig();
        $this->loadAlias();
        $this->loadRoutes();
        $this->request = new Request;
    }

    /**
     * Load configs.
     *
     * @return void
     */
    private function loadConfig()
    {
        $dotenv = Dotenv::createUnsafeImmutable(base_path());
        $dotenv->load();

        $configs = [];
        $configFiles = Storage::files(base_path() . "/config", "*.php");
        foreach ($configFiles as $file) {
            $fileName = substr($file, strrpos($file, "/") + 1);
            $configs += [substr($fileName, 0, strrpos($fileName, ".")) => @include $file];
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
        $this->configs ?: $this->loadConfig();
        $this->aliases = $this->configs['app']['aliases'];
        foreach ($this->aliases as $alias => $class) {
            class_alias($class, $alias);
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

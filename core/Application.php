<?php

namespace Core;

use Dotenv\Dotenv;
use RuntimeException;
use Core\Support\Facades\Route;
use Core\Pattern\Singleton;
use Core\Contract\Provider;
use Core\Support\Helper\Str;
use Core\Container\Container;
use Spatie\Ignition\Ignition;
use Core\Support\Facades\Response;
use Core\Http\Exception\HttpException;

class Application extends Container
{
    use Singleton;

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
     * Bootstrap application
     *
     * @return void
     */
    private function bootstrap()
    {
        self::setInstance($this);
        $this->loadConfig();
        $this->loadAlias();
        $this->loadProvider();
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
     * Dispatch application.
     *
     * @return mixed;
     */
    public function dispatch()
    {
        $this->registerShutdownFunction();

        try {
            $response = Route::resolveRoute()->run();
        } catch (\Throwable $th) {
            if (! $th instanceof HttpException) {
                throw $th;
            }
            $response = $th->response();
        }
        if ($response instanceof \Core\Views\ViewInterface) {
            Response::setContent($response->render());
        } elseif (!$response instanceof \Core\Http\Response && !Response::isRedirection()) {
            Response::setContent($response);
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

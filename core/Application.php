<?php

namespace Core;

use Dotenv\Dotenv;
use Core\Routing\Route;
use Spatie\Ignition\Ignition;
use Core\Http\Request;
use Core\Pattern\Singleton;
use Core\Support\Facades\Response;
use Core\Http\Exception\HttpException;
use Core\Support\Storage;

class Application
{
    use Singleton;

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
        self::$instance = $this;
    }

    /**
     * Load configs.
     *
     * @return void
     */
    private function loadConfig()
    {
        $dotenv = Dotenv::createUnsafeImmutable(root_path());
        $dotenv->load();

        $configs = [];
        $configFiles = Storage::files(root_path() . "/config", "*.php");
        foreach ($configFiles as $file) {
            $fileName = substr($file, strrpos($file, "/") + 1);
            $configs += [substr($fileName, 0, strrpos($fileName, ".")) => require $file];
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
        require root_path() . "/routes/web.php";

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
            $response = Route::dispatch();
        } catch (\Throwable $th) {
            if (! $th instanceof HttpException) {
                throw $th;
            }
            $response = $th->response();
        }

        Response::setContent($response)->send();
    }

    /**
     * Register shutdown function.
     *
     * @return mixed
     */
    private function registerShutdownFunction()
    {
        Ignition::make()->register();
        register_shutdown_function(function() {
            $lastError = error_get_last();
            if (!is_null($lastError) && in_array($lastError['type'], [E_COMPILE_ERROR, E_CORE_ERROR, E_ERROR, E_PARSE])) {
                $exception = new \Core\Error\ErrorHandle($lastError['message'], 0, $lastError, 0);
                return call_user_func([Ignition::make(), 'handleException'], $exception);
            }
        });
    }
}

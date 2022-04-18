<?php

namespace Core;

use Dotenv\Dotenv;
use Core\Routing\Route;
use Spatie\Ignition\Ignition;

class Application
{
    /**
     * The current globally available container (if any).
     *
     * @var static
     */
    protected static $instance;

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
        $this->loadConfig();
        $this->loadAlias();
        $this->loadRoutes();
        self::$instance = $this;
    }

    /**
     * Load configs.
     *
     * @return void
     */
    private function loadConfig()
    {
        $configs = require root_path() . "/config/app.php";
        $dotenv = Dotenv::createImmutable(root_path());
        $dotenv->safeLoad();
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
        $this->aliases = $this->configs['aliases'];
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
     * Get the globally available instance of the container.
     *
     * @return static
     */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    /**
     * Dispatch application.
     *
     * @return mixed;
     */
    public function dispatch()
    {
        $this->registerShutdownFunction();

        return Route::dispatch();
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

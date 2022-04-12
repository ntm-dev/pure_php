<?php

namespace Core;

use Dotenv\Dotenv;
use Core\ApplicationException;
use Core\Routing\Route;

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

    private function bootstrap()
    {
        error_reporting(0);
        $this->loadConfig();
        $this->loadAlias();
        $this->loadRoutes();
        self::$instance = $this;
    }

    private function loadConfig()
    {
        $configs = require root_path() . "/config/app.php";
        $dotenv = Dotenv::createImmutable(root_path());
        $dotenv->safeLoad();
        $this->configs = array_merge($configs, $_ENV);
    }

    public function getConfig()
    {
        return $this->configs;
    }

    private function loadAlias()
    {
        $this->configs ?: $this->loadConfig();
        $this->aliases = $this->configs['aliases'];
        foreach ($this->aliases as $alias => $class) {
            class_alias($class, $alias);
        }
    }

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

    public function dispatch()
    {
        try {
            return Route::dispatch();
        } catch (\Throwable $th) {
            throw new ApplicationException($th);
        }
    }
}

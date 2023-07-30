<?php

namespace Core\Database\Connectors;

use RuntimeException;
use Core\Database\Connectors\MysqlConnector;

class Connector
{
    /** @var string */
    protected $connectionName;

    /**
     * The database connection instance.
     *
     * @var \Support\Contract\DatabaseConnector
     */
    protected static $connection;

    public function __construct($connectionName = '')
    {
        $this->connectionName = $connectionName ?: config('database.default');
    }

    /**
     * Create a new database connection.
     *
     * @return \Support\Contract\DatabaseConnector|false
     */
    public function connect()
    {
        $connectionName = $this->getConnectionName();
        if (isset(self::$connection[$connectionName])) {
            return self::$connection[$connectionName];
        }

        $config = config("database.connections.{$connectionName}");
        if (!$config) {
            throw new RuntimeException("{$connectionName} is not defined.");
        }
        $method = 'create' . ucfirst($config["driver"]) . 'Connection';

        $dbh = $this->{$method}($config);

        return self::$connection[$connectionName] = $dbh;
    }

    public function getConnectionName()
    {
        return $this->connectionName ?: config('database.default');
    }

    public function setConnectionName($connectionName)
    {
        return $this->connectionName = $connectionName;
    }

    /**
     * Create a new PDO connection.
     *
     * @var array config
     * @return \Support\Database\Connectors\MysqlConnector
     *
     * @throws \Exception
     */
    public function createMysqlConnection(array $config)
    {
        return (new MysqlConnector($config))->connect();
    }
}

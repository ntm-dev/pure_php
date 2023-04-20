<?php

namespace Core\Database\Connectors;

use PDO;
use PDOException;

class MysqlConnector
{
    /** @var string */
    protected $connectionName;

    /**
     * The database connection instance.
     *
     * @var \PDO
     */
    protected static $connection;

    /**
     * The database configuration.
     */
    protected $configs = [];

    /**
     * The default PDO connection options.
     *
     * @var array
     */
    protected $options = [
        PDO::ATTR_CASE => PDO::CASE_NATURAL, // Leave column names as returned by the database driver.
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Set error reporting mode of PDO to Throws PDOExceptions.
        PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL, // No conversion takes place.
        PDO::ATTR_STRINGIFY_FETCHES => false, // No convert numeric values to strings when fetching.
        PDO::ATTR_EMULATE_PREPARES => false, // Turn off emulate prepared statements, to true PDO will always emulate prepared statements, otherwise PDO will attempt to use native prepared statements instance.
    ];

    public function __construct($configs = '')
    {
        $this->configs = $configs;
    }

    /**
     * Create a new database connection.
     *
     * @return PDO|false
     */
    public function connect()
    {
        try {
            $dbh = $this->createConnection(
                $this->getDns(),
                $this->getConfig('username'),
                $this->getConfig('password'),
                $this->getOptions()
            );
        } catch (PDOException $e) {
            $dbh = false;
        }

        return self::$connection = $dbh;
    }
    /**
     * Create a new PDO connection.
     *
     * @param  string  $dsn
     * @param  string  $username
     * @param  string  $password
     * @param  array  $options
     * @return \PDO
     *
     * @throws \Exception
     */
    public function createConnection($dsn, $username = null, $password = null, $options = [])
    {
        try {
            return new PDO($dsn, $username ?: config('DB_USERNAME'), $password ?: config('DB_PASSWORD'), $options);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Create a DSN string from a configuration.
     *
     * @return string
     */
    public function getDns()
    {
        return $this->getConfig('driver') . ":host=" . $this->getConfig('host') . "; port=" . $this->getConfig('port') . "; dbname=" . $this->getConfig('database');
    }

    /**
     * Get the PDO options based on the configuration.
     *
     * @param  array  $options
     * @return array
     */
    public function getOptions(array $options = [])
    {
        return $this->options + $options;
    }

    /**
     * Get the default PDO connection options.
     *
     * @return array
     */
    public function getDefaultOptions()
    {
        return $this->options;
    }

    /**
     * Set the default PDO connection options.
     *
     * @param  array  $options
     * @return void
     */
    public function setDefaultOptions(array $options)
    {
        $this->options = $options;
    }

    protected function getConfigs()
    {
        return $this->configs ?: $this->configs = config('database.connections.' . config('database.default'));
    }

    protected function getConfig($key)
    {
        $configs = $this->getConfigs();

        return isset($configs[$key]) ? $configs[$key] : null;
    }
}

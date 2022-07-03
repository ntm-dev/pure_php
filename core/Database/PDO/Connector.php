<?php

namespace Core\Database\PDO;

use PDO;
use PDOException;

trait Connector
{
    /**
     * The database connection instance.
     *
     * @var PDO
     */
    protected static $connection;

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

    /**
     * Create a new database connection.
     *
     * @return PDO|false
     */
    public function connect()
    {
        try {
            $connectionString = config('DB_CONNECTION') . ":host=" . config('DB_HOST') . "; port=" . config('DB_PORT') . "; dbname=" . config('DB_DATABASE');
            $dbh = new PDO($connectionString, config('DB_USERNAME'), config('DB_PASSWORD'), $this->options);
        } catch (PDOException $e) {
            $dbh = false;
        }

        return self::$connection = $dbh;
    }
}

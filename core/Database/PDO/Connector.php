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
     * Create a new database connection.
     *
     * @return PDO|false
     */
    public function connect()
    {
        try {
            $connectionString = config('DB_CONNECTION') . ":host=" . config('DB_HOST') . "; port=" . config('DB_PORT') . "; dbname=" . config('DB_DATABASE');
            $dbh = new PDO($connectionString, config('DB_USERNAME'), config('DB_PASSWORD'));
        } catch (PDOException $e) {
            $dbh = false;
        }

        return self::$connection = $dbh;
    }
}

<?php

use Core\Support\Helper\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => getenv('DB_CONNECTION') ?: 'mysql',

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    */

    'connections' => [

        'mysql' => [
            'driver' => 'mysql',
            'host' => getenv('DB_HOST') ?: '127.0.0.1',
            'port' => getenv('DB_PORT') ?: '3306',
            'database' => getenv('DB_DATABASE') ?: 'forge',
            'username' => getenv('DB_USERNAME') ?: 'forge',
            'password' => getenv('DB_PASSWORD') ?: '',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    */

    'redis' => [

        'default' => [
            'host' => getenv('REDIS_HOST') ?: '127.0.0.1',
            'password' => getenv('REDIS_PASSWORD') ?: '127',
            'port' => getenv('REDIS_PORT') ?: '6379',
            'database' => getenv('REDIS_DB') ?: '0',
        ],
        'options' => [
            'cluster' => getenv('REDIS_CLUSTER') ?: 'redis',
            'prefix' => getenv('REDIS_PREFIX') ?: Str::slug(getenv('APP_NAME') ?: 'pure_php', '_').'_database_',
        ],

    ],

];

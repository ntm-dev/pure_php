<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that gets used when writing
    | messages to the logs. The name specified in this option should match
    | one of the channels defined in the "channels" configuration array.
    |
    */

    'default' => getenv('LOG_CHANNEL') ?: 'daily',

    /*
    |--------------------------------------------------------------------------
    | Log Directory
    |--------------------------------------------------------------------------
    */

    'dir' => getenv('LOG_DIR') ?: storage_path("logs"),

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application.
    |
    | Available Drivers: "single", "daily"
    |
    */

    'channels' => [
        'single' => [
            'filename' => 'app',
        ],

        'daily' => [
            'filename' => 'app',
            'days' => 14,
        ],

        /* Only use cloudwatch for fatal application error */
        'cloudwatch' => [
            'name' => 'REPITTE_FATAL_ERROR',
        ],
    ],

];

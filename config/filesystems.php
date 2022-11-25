<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "s3" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => getenv('FILESYSTEM_DISK') ?: 'local',

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local, s3"
    |
    */

    'disks' => [
        'local' => [
            'root' => storage_path('app'),
        ],
        's3' => [
            'version' => 'latest',
            'region'  => getenv('AWS_DEFAULT_REGION'),
            'bucket'  => getenv('AWS_BUCKET'),
            'bucket_private'  => getenv('AWS_BUCKET_PRIVATE'),
        ],
    ],
];

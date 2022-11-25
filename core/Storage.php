<?php

namespace Core;

use InvalidArgumentException;
use Core\File\AwsS3Adapter;
use Core\File\LocalAdapter;

/**
 * Storage class.
 *
 * @author Nguyen The Manh <nguyenthemanh26011996@gmail.com>
 */
class Storage
{
    public function getConfig($key = '')
    {
        if ($key) {
            return Config::get("filesystems.{$key}");
        }

        return Config::get('filesystems');
    }

    public function getCurrentDisk()
    {
        return $this->getConfig('default');
    }

    public function disk($name = '')
    {
        $name = $name ?: $this->getConfig("default");

        if (! in_array( $name, array_keys( $this->getConfig('disks') ) ) ) {
            throw new InvalidArgumentException(sprintf("Disk %s do not support", $name));
        }

        switch ($name) {
            case 's3':
                return new AwsS3Adapter;
            default:
                return new LocalAdapter;
        }
    }

    public function __call($method, $arguments)
    {
        return $this->disk()->$method(...$arguments);
    }

}
<?php

namespace Core\File;

use Core\Config;

/**
 * Abstract Adapter.
 *
 * @author Nguyen The Manh <nguyenthemanh26011996@gmail.com>
 */
abstract class Adapter implements AdapterInterface
{
    /** @var array */
    protected $configs = [];

    public function __construct()
    {
        $this->configs = $this->getConfig();
    }

    public function getConfig($key = '')
    {
        if ($key) {
            return Config::get("filesystems.{$key}");
        }

        return Config::get('filesystems');
    }

    /**
     * Store the uploaded file on the disk.
     *
     * @param  string  $path
     * @param  array  $options
     * @return string|false
     */
    public function putFile($path, $options = [])
    {
        if (! file_exists($path)) {
            throw new \LogicException(sprintf("File %s do not exist.", $path));
        }

        return $this->put($path, file_get_contents($path), $options);
    }
}

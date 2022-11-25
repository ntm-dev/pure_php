<?php

namespace Core\File;

use RuntimeException;
use Core\File\Detector;
use Core\Support\Helper\Str;

/**
 * Local file adapter.
 *
 * @author Nguyen The Manh <nguyenthemanh26011996@gmail.com>
 */
class LocalAdapter extends Adapter
{
    const PERMISSION_FILE_PUBLIC = 0644;
    const PERMISSION_FILE_PRIVATE = 0600;
    const PERMISSION_DIRECTORY_PUBLIC = 0755;
    const PERMISSION_DIRECTORY_PRIVATE = 0700;

    /** @var  string */
    private $rootPath;

    /** @var  \Core\File\Detector */
    private $fileDetector;

    public function __construct()
    {
        parent::__construct();
        $this->fileDetector = new Detector;
    }

    public function root($path = '')
    {
        if ($path) {
            $this->rootPath = $path;
        } else {
            $this->rootPath = $this->getConfig('root');
        }

        return $this;
    }

    private function getRootPath()
    {
        if (!$this->rootPath) {
            $this->root();
        }

        return $this->rootPath;
    }

    public function url($path)
    {
        if (Str::startsWith($path, $this->getRootPath())) {
            $path = str_replace($this->getRootPath() . DIRECTORY_SEPARATOR, '', $path);
        }

        $path = array_filter(explode(DIRECTORY_SEPARATOR, $path));

        return "/" . implode(DIRECTORY_SEPARATOR, $path);
    }

    public function exists($path)
    {
        return is_file($this->correctPath($path));
    }

    public function missing($path)
    {
        return !$this->exists($path);
    }

    public function path($path)
    {
        return $this->getRootPath() . DIRECTORY_SEPARATOR . $path;
    }

    public function get($path)
    {
        $path = $this->correctPath($path);
        $contents = @file_get_contents($path);

        if ($contents === false) {
            throw new RuntimeException("Unable to read file from location: {$path}");
        }

        return $contents;
    }

    public function put($path, $contents, $options = []) 
    {
        $prefixedLocation = $this->correctPath($path);
        $this->ensureDirectoryExists(dirname($prefixedLocation), self::PERMISSION_DIRECTORY_PUBLIC);
        $contents = $this->fileDetector->detectContent($contents);

        if (false === @file_put_contents($prefixedLocation, $contents, LOCK_EX)) {
            throw new RuntimeException("Unable to write file at to: {$path}");
        }

        return true;
    }

    public function putFile($path, $options = []) 
    {
        return parent::putFile($path, $options);
    }

    public function delete($paths)
    {
        $paths = is_array($paths) ? $paths : func_get_args();

        $success = true;

        foreach ($paths as $path) {
            $success = @unlink($this->correctPath($path));
        }

        return $success;
    }

    public function copy($from, $to)
    {
        $to = $this->correctPath($to);
        $from = $this->correctPath($from);

        if (false === @copy($from, $to)) {
            throw new RuntimeException("Unable to copy file from {$from} to {$to}");
        }

        return true;
    }

    public function move($from, $to)
    {
        $to = $this->correctPath($to);
        $from = $this->correctPath($from);

        if (false === @rename($from, $to)) {
            throw new RuntimeException("Unable to move file from {$from} to {$to}");
        }

        return true;
    }

    public function size($path)
    {
        $path = $this->correctPath($path);

        if (is_file($path) && ($fileSize = @filesize($path)) !== false) {
            return $fileSize;
        }

        throw new RuntimeException("Unable to retrieve the size for file at location: {$path}");
    }

    public function mimeType($path)
    {
        $path = $this->correctPath($path);
        $mimeType = $this->fileDetector->detectMimeTypeFromFile($path);

        if ($mimeType === null) {
            throw new RuntimeException("Unable to retrieve the mime_type for file at location: {$path}");
        }

        return $mimeType;
    }

    public function lastModified($path)
    {
        $path = $this->correctPath($path);
        $lastModified = @filemtime($path);

        if ($lastModified === null) {
            throw new RuntimeException("Unable to retrieve the last modified for file at location: {$path}");
        }

        return $lastModified;
    }

    public function readStream($path)
    {
        $path = $this->correctPath($path);
        $contents = @fopen($path, 'rb');

        if (false === $contents) {
            throw new RuntimeException("Unable to read file from location: {$path}");
        }

        return $contents;
    }

    public function files($directory = '', $recursive = true, $toArray = true)
    {
        $path = $this->correctPath($directory);
        $files = @array_diff(@scandir($path), array('.', '..')) ?: [];

        $result = [];
        $trimLength = strlen($this->getRootPath()) + 1;

        foreach ($files as $file) {
            if (!$recursive && is_dir($path . DIRECTORY_SEPARATOR . $file)) {
                continue;
            }
            if (is_dir($path . DIRECTORY_SEPARATOR . $file)) {
                $result = array_merge($result, array_map(function($val) use ($path, $file, $trimLength) {
                    $realPath = $path . DIRECTORY_SEPARATOR . $file . DIRECTORY_SEPARATOR . $val;
                    return substr($realPath, $trimLength);
                }, $this->allFiles($path . DIRECTORY_SEPARATOR . $file)));
            } else {
                $result[] = $file;
            }
        }
        if ($toArray) {
            return $result;
        }

        $generators = $this->createGenerators($result);

        return $generators;
    }

    private function createGenerators($data)
    {
        foreach ($data as $value) {
            yield $value;
        }
    }

    public function allFiles($prefix = '', $recursive = true, $toArray = true)
    {
        if (is_dir($this->correctPath($prefix))) {
            return array_map(function ($val) use ($prefix) {
                return $this->correctPath($prefix) . DIRECTORY_SEPARATOR . $val;
            }, $this->files($prefix, $recursive, $toArray));
        }

        $arrPart = explode(DIRECTORY_SEPARATOR, $prefix);
        array_pop($arrPart);
        $directory = implode(DIRECTORY_SEPARATOR, $arrPart);

        $result = [];
        $files = $this->files($directory, $recursive, $toArray);

        foreach ($files as $file) {
            $filePath = $directory . DIRECTORY_SEPARATOR . $file;
            if (Str::startsWith($filePath, $prefix)) {
                $result[] = $filePath;
            }
        }

        if ($toArray) {
            return $result;
        }

        $generators = $this->createGenerators($result);

        return $generators;
    }

    /**
     * Correct path.
     *
     * @param  string  $path
     * @return string
     */
    private function correctPath($path)
    {
        $rootPath = $this->getRootPath();
        if (!$path) {
            return $rootPath;
        }

        if (Str::startsWith($path, $rootPath)) {
            $path = str_replace($rootPath . DIRECTORY_SEPARATOR, '', $path);
        }

        $path = array_filter(explode(DIRECTORY_SEPARATOR, $path));

        return $rootPath . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $path);
    }

    /**
     * Ensure directory exists.
     *
     * @param  string  $dirname
     * @param  int  $visibility
     * @return void
     */
    private function ensureDirectoryExists($dirname, $visibility)
    {
        if (is_dir($dirname)) {
            return;
        }

        if (! @mkdir($dirname, $visibility, true)) {
            $mkdirError = error_get_last();
        }

        clearstatcache(true, $dirname); // https://www.php.net/manual/en/function.clearstatcache

        if (! is_dir($dirname)) {
            $errorMessage = isset($mkdirError['message']) ? $mkdirError['message'] : '';

            throw new RuntimeException("Unable to create a directory at {$dirname}. {$errorMessage}");
        }
    }

    public function privateDriver()
    {
        return $this;
    }

    public function directories($directory = '', $recursive = false)
    {
        $location = $this->correctPath($directory);
        $files = @array_diff(@scandir($location), ['.', '..']) ?: [];

        $directories = [];
        $trimLength = strlen($this->getRootPath()) + 1;

        foreach ($files as $value) {
            if (!is_dir($location . DIRECTORY_SEPARATOR . $value)) {
                continue;
            }
            $directories[] = $value;
            if ($recursive) {
                $directories = array_merge($directories, array_map(function($val) use ($location, $value, $trimLength) {
                    $realPath = $location . DIRECTORY_SEPARATOR . $value . DIRECTORY_SEPARATOR . $val;
                    return substr($realPath, $trimLength);
                }, $this->directories($location . DIRECTORY_SEPARATOR . $value)));
            }
        }

        return $directories;
    }

    public function makeDirectory($path)
    {
        $location = $this->correctPath($path);

        if (is_dir($location)) {
            return true;
        }

        if (! @mkdir($location, self::PERMISSION_DIRECTORY_PUBLIC, true)) {
            throw new RuntimeException("Unable to create directory located at: {$location}.");
        }

        return true;
    }

    public function deleteDirectory($directory)
    {
        $location = $this->correctPath($directory);

        if (! is_dir($location)) {
            return;
        }

        $contents = $this->allFiles($location);

        foreach ($contents as $file) {
            if (! $this->delete($file)) {
                throw new  RuntimeException("Unable to delete directory located at: {$file}.");
            }
        }

        unset($contents);

        if (! @rmdir($location)) {
            throw new  RuntimeException("Unable to delete directory located at: {$location}.");
        }

        return true;
    }

    /**
     * Delete directory.
     *
     * @param  string  $prefix
     * @return bool
     */
    public function deleteByPrefix($prefix)
    {
        try {
            $objects = $this->allFiles($prefix);
        } catch (\Throwable $th) {
            $objects = [];
        }

        if (! empty($objects)) {
            return $this->delete($objects);
        }

        return true;
    }

    public function bucket()
    {
        return $this;
    }

    /**
     * Append to a file.
     *
     * @param  string  $path
     * @param  string  $data
     * @param  string  $separator
     * @return bool
     */
    public function append($path, $contents, $separator = PHP_EOL)
    {
        $prefixedLocation = $this->correctPath($path);
        if ($this->exists($prefixedLocation)) {
            return $this->put($prefixedLocation, $this->get($prefixedLocation).$separator.$contents);
        }

        return $this->put($prefixedLocation, $contents);
    }
}
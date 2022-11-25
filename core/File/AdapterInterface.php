<?php

namespace Core\File;

/**
 * Adapter interface.
 *
 * @author Nguyen The Manh <nguyenthemanh26011996@gmail.com>
 */
interface AdapterInterface
{
    /**
     * Write the contents of a file.
     *
     * @param  string  $path
     * @param  FileSystem  $contents
     * @param  array  $options
     * @return bool
     */
    public function put($path, $contents, $options = []);

    /**
     * Delete the file at a given path.
     *
     * @param  string|array  $paths
     * @return bool
     */
    public function delete($paths);

    /**
     * Copy a file to a new location.
     *
     * @param  string  $from
     * @param  string  $to
     * @return bool
     */
    public function copy($from, $to);

    /**
     * Move a file to a new location.
     *
     * @param  string  $from
     * @param  string  $to
     * @return bool
     */
    public function move($from, $to);

    /**
     * Get all of the files from the given directory (recursive).
     *
     * @param  string  $directory
     * @param  bool  $recursive
     * @param  bool  $toArray
     * @return array|\Generator
     */
    public function allFiles($prefix = '', $recursive = false, $toArray = true);

    /**
     * Get the file size of a given file.
     *
     * @param  string  $path
     * @return int
     */
    public function size($path);

    /**
     * Get the mime-type of a given file.
     *
     * @param  string  $path
     * @return string|false
     */
    public function mimeType($path);

    /**
     * Get the file's last modification time.
     *
     * @param  string  $path
     * @return int
     */
    public function lastModified($path);


    /**
     * Get a resource to read the file.
     *
     * @param  string  $path
     * @return resource|null — The path resource or null on failure.
     */
    public function readStream($path);

    /**
     * Get an array of all files in a directory.
     *
     * @param  string  $directory
     * @param  bool  $recursive
     * @param  bool  $toArray
     * @return array|\Generator
     */
    public function files($directory = '', $recursive = true, $toArray = true);

    /**
     * Get the URL for the file at the given path.
     *
     * @param  string  $path
     * @return string
     *
     * @throws \RuntimeException
     */
    public function url($path);

    /**
     * Get the contents of a file.
     *
     * @param  string  $path
     * @return string|null
     */
    public function get($path);

    /**
     * Determine if a file exists.
     *
     * @param  string  $path
     * @return bool
     */
    public function exists($path);

    /**
     * Determine if a file missing.
     *
     * @param  string  $path
     * @return bool
     */
    public function missing($path);

    /**
     * Get the full path for the file at the given "short" path.
     *
     * @param  string  $path
     * @return string
     */
    public function path($path);

    /**
     * Set bugket to private.
     *
     * @return self
     */
    public function privateDriver();

    /**
     * Get all of the directories within a given directory.
     *
     * @param  string  $directory
     * @param  bool  $recursive
     * @return array
     */
    public function directories($directory = '', $recursive = false);

    /**
     * Create a directory.
     *
     * @param  string  $path
     * @return bool
     */
    public function makeDirectory($path);

    /**
     * Delete a directory.
     *
     * @param  string  $directory
     * @return bool
     */
    public function deleteDirectory($directory);
}

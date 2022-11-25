<?php

namespace Core\File;

use Exception;
use Aws\S3\S3Client;
use RuntimeException;
use Support\Helper\Str;
use Support\File\Detector;

/**
 * Aws S3 Adapter.
 *
 * @author Nguyen The Manh <nguyenthemanh26011996@gmail.com>
 */
final class AwsS3Adapter extends Adapter
{
    /** @var string */
    const DEFAULT_TEMPORARY_URL_EXPIRATION = "+10 minutes";

    /** @var \Aws\S3\S3Client */
    private $client;

    /** @var  \Core\File\Detector */
    private $fileDetector;

    /** @var  array */
    private static $cachedObject = [];

    /** @var  array */
    private static $cachedHeadObject = [];

    public function __construct()
    {
        parent::__construct();
        $this->init();
    }

    private function init()
    {
        $this->configs = $this->configs['disks']['s3'];
        $this->client = new S3Client([
            'region'  => $this->configs['region'],
            'version' => $this->configs['version'],
        ]);
        $this->fileDetector = new Detector;
    }

    public function url($path)
    {
        return $this->client->getObjectUrl($this->getBucket(), $path);
    }

    public function exists($path)
    {
        try {
            return $this->client->doesObjectExistV2($this->getBucket(), $path);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function missing($path)
    {
        return !$this->exists($path);
    }

    public function path($path)
    {
        return $this->correctPath($path);
    }

    public function get($path)
    {
        try {
            $body = $this->readObject($path)->get('Body');

            return (string) $body->getContents();
        } catch (Exception $e) {
        }
    }

    public function put($path, $contents, $options = [])
    {
        try {
            $this->client->putObject([
                'Bucket'      => $this->getBucket(),
                'Key'         => $this->correctPath($path),
                'Body'        => $this->fileDetector->detectContent($contents),
                'ContentType' => $this->fileDetector->detectMimeType($contents)
            ]);
        } catch (Exception $e) {
            return false;
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
            try {
                $this->client->deleteObject([
                    'Bucket' => $this->getBucket(),
                    'Key'    => $this->correctPath($path),
                ]);
            } catch (Exception $e) {
                $success = false;
            }
        }

        return $success;
    }

    public function copy($from, $to)
    {
        $options = [
            'Bucket'     => $this->getBucket(),
            'Key'        => $this->correctPath($to),
            'CopySource' => $this->getBucket() . "/" . $this->correctPath($from),
        ];
        try {
            $this->client->copyObject($options);
        } catch (\Throwable $th) {
            return false;
        }

        return true;
    }

    public function move($from, $to)
    {
        if ($this->copy($from, $to)) {
            return $this->delete($from);
        }

        return false;
    }

    public function size($path)
    {
        try {
            return $this->headObject($path)->get('ContentLength');
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function mimeType($path)
    {
        try {
            return $this->headObject($path)->get('ContentType');
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function lastModified($path)
    {
        try {
            $lastModified = $this->headObject($path)->get('LastModified');

            return $lastModified->getTimestamp();
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @param  string  $path
     * @return $mixed
     */
    public function readStream($path)
    {
        $options = [
            'Bucket' => $this->getBucket(),
            'Key'    => $this->correctPath($path),
            '@http'  => ['stream' => true],
        ];

        $command = $this->client->getCommand('GetObject', $options + $this->options);

        try {
            return $this->client->execute($command)->get('Body')->detach();
        } catch (Exception $e) {
            throw new RuntimeException("Unable to read file from location: {$path}");
        }
    }

    public function files($directory = '', $recursive = true, $toArray = true)
    {
        $options = [
            "Bucket" => $this->getBucket(),
        ];

        if ($directory) {
            $options["Prefix"] = $this->correctPath($directory);
        }

        try {
            $files = $this->client->getIterator('ListObjects', $options);

            if ($toArray) {
                return iterator_to_array($files);
            }

            return $files;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function allFiles($prefix = '', $recursive = true, $toArray = true)
    {
        return $this->files($prefix, $recursive, $toArray);
    }

    public function privateDriver()
    {
        return $this->bucket($this->configs['bucket_private']);
    }

    public function directories($directory = '', $recursive = false)
    {
        $directories = [];
        $files = $this->allFiles($directory, false);

        foreach ($files as $file) {
            list($dir) = explode(DIRECTORY_SEPARATOR, $file['Key'], 2);
            if ($dir && !in_array($dir, $directories)) {
                $directories[] = $dir;
            }
        }

        return $directories;
    }

    public function makeDirectory($path)
    {
        try {
            $this->client->putObject([
                'Bucket' => $this->getBucket(),
                'Key'    => $this->correctPath($path) . DIRECTORY_SEPARATOR,
                'Body'   => "",
            ]);
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Put file using queue.
     *
     * @return bool
     */
    /**
    public function putAsync($path)
    {
        return !!RedisService::add(getenv("AWS_PUT_FILE_QUEUE_NAME"), [
            'file' => $path,
            'bucket' => $this->getBucket(),
        ]);
    }
     */

    /**
     * Correct path.
     *
     * @param  string  $path
     * @return string
     */
    private function correctPath($path)
    {
        if (Str::startsWith($path, public_path())) {
            $path = str_replace(public_path() . DIRECTORY_SEPARATOR, '', $path);
        }
        $path = array_filter(explode(DIRECTORY_SEPARATOR, $path));

        return implode(DIRECTORY_SEPARATOR, $path);
    }

    public function deleteDirectory($directory)
    {
        $directory = trim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        return $this->deleteByPrefix($directory);
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

        if (!empty($objects)) {
            return $this->delete(array_map(function ($object) {
                return $object['Key'];
            }, $objects));
        }

        return true;
    }

    public function temporaryUrl($path, $expiration = null, array $options = [])
    {
        $command = $this->client->getCommand('GetObject', array_merge([
            'Bucket' => $this->getBucket(),
            'Key'    => $path,
        ], $options));

        $uri = $this->client->createPresignedRequest(
            $command,
            $expiration ?: self::DEFAULT_TEMPORARY_URL_EXPIRATION
        )->getUri();

        return (string) $uri;
    }

    public function readObject($path, $range = null)
    {
        try {
            $options = [
                'Bucket' => $this->getBucket(),
                'Key'    => $this->correctPath($path)
            ];
            if ($range) {
                $options['Range'] = $range;
            }
            if ($cached = $this->objectCached($options)) {
                return $cached;
            }

            $options['object'] = $this->client->getObject($options);
            if ($options['object']) {
                self::$cachedObject[] = $options; // add object to cache
            }

            return $options['object'];
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get head object.
     *
     * @param  string  $path
     * @return \Aws\Result
     *
     * @throws \Aws\S3\Exception\S3Exception
     */
    public function headObject($path)
    {
        try {
            $options = [
                'Bucket' => $this->getBucket(),
                'Key'    => $path
            ];
            if ($cached = $this->objectHeadCached($options)) {
                return $cached;
            }

            $options['object'] = $this->client->HeadObject($options);
            if ($options['object']) {
                self::$cachedHeadObject[] = $options; // add object to cache
            }

            return $options['object'];
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function bucket($name)
    {
        $this->configs['bucket'] = $name;

        return $this;
    }

    /**
     * Get config bucket
     *
     * @return string|null
     */
    public function getBucket()
    {
        return $this->configs['bucket'];
    }

    private function objectCached($options, $cacher = null)
    {
        $cacher = $cacher ?: self::$cachedObject;
        foreach (self::$cachedObject as $value) {
            if (!isset($value['object']) || (isset($value['Range']) && !isset($options['Range']))) {
                continue;
            }
            if ($options['Key'] == $value['Key'] && $options['Bucket'] == $value['Bucket']) {
                return $value['object'];
            }
        }

        return false;
    }

    private function objectHeadCached($options)
    {
        return $this->objectCached($options) ?: $this->objectCached($options, self::$cachedHeadObject);
    }
}

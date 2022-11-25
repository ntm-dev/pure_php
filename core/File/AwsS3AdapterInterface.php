<?php

namespace Core\File;

/**
 * Aws S3 Adapter interface.
 *
 * @author Nguyen The Manh <nguyenthemanh26011996@gmail.com>
 */
interface AwsS3AdapterInterface extends AdapterInterface
{
    /**
     * Set bucket.
     *
     * @param  string  $name
     * @return self
     */
    public function bucket($name);

    /**
     * Get a temporary URL for the file at the given path.
     *
     * @param  string  $path
     * @param  int|string|\DateTimeInterface  $expiration
     * @param  array  $options
     * @return string
     */
    public function temporaryUrl($path, $expiration = null, array $options = []);

    /**
     * Read object.
     *
     * @param  string  $path
     * @return \Aws\Result
     * 
     * @throws \Aws\S3\Exception\S3Exception
     */
    public function readObject($path);
}

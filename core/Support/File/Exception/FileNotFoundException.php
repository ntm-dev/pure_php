<?php

namespace Support\File\Exception;

/**
 * Thrown when a file was not found.
 *
 * @author Nguyen The Manh <manh.nguyen3@ntq-solution.com.vn>
 */
class FileNotFoundException extends FileException
{
    public function __construct(string $path)
    {
        parent::__construct(sprintf('The file "%s" does not exist', $path));
    }
}

<?php

namespace Core\File;

use finfo;
use Imagick;
use Core\File\MimeTypes;
use Core\File\FileSystem;

/**
 * File detector.
 *
 * @author Nguyen The Manh <nguyenthemanh26011996@gmail.com>
 */
class Detector
{
    /** @var finfo */
    private $finfo;

    /** @var MimeTypes */
    private $mimeTypes;

    public function __construct()
    {
        $this->finfo = new finfo(FILEINFO_MIME_TYPE);
        $this->mimeTypes = new MimeTypes;
    }

    public function detectContent($file)
    {
        if (extension_loaded('Imagick') && $file instanceof Imagick) {
            return $file->getImagesBlob();
        }
        if ($file instanceof FileSystem) {
            return $file->getContent();
        }
        if (is_resource($file)) {
            return stream_get_contents($file);
        }

        return $file;
    }

    public function detectMimeType($contents)
    {
        if (extension_loaded('Imagick') && $contents instanceof Imagick) {
            return $contents->getImageMimeType();
        }

        if ($contents instanceof FileSystem) {
            return $contents->getMimeType();
        }

        if (is_resource($contents)) {
            return $this->detectMimeTypeFromBuffer($contents);
        }

        return is_string($contents)
            ? (@$this->finfo->buffer($contents) ?: null)
            : null;
    }

    public function detectMimeTypeFromPath($path)
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return $this->mimeTypes->getMimeTypes($extension);
    }

    public function detectMimeTypeFromFile($path)
    {
        return @$this->finfo->file($path) ?: null;
    }

    public function detectMimeTypeFromBuffer($contents)
    {
        return @$this->finfo->buffer($contents) ?: null;
    }

}

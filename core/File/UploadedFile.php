<?php

namespace Core\File;

use Core\File\FileSystem;
use Core\File\Exception\FileException;
use Core\File\Exception\FileNotFoundException;

/**
 * A file uploaded through a form.
 * 
 * @author Nguyen The Manh <nguyenthemanh26011996@gmail.com>
 */
class UploadedFile extends FileSystem
{
    private $originalName;
    private $mimeType;
    private $error;
    /**
     * The cache copy of the file's hash name.
     *
     * @var string
     */
    protected $hashName = null;

    /**
     * Accepts the information of the uploaded file as provided by the PHP global $_FILES.
     * 
     * @throws FileException         If file_uploads is disabled
     * @throws FileNotFoundException If the file does not exist
     */
    public function __construct($path, $originalName, $error = null)
    {
        parent::__construct($path, \UPLOAD_ERR_OK === $this->error);
        $this->originalName = $this->getName($originalName);
        $this->mimeType = $this->getMimeType();
        $this->error = $error ?: \UPLOAD_ERR_OK;
    }


    /**
     * Returns the original file extension.
     *
     * It is extracted from the original file name that was uploaded.
     * Then it should not be considered as a safe value.
     *
     * @return string The extension
     */
    public function getClientOriginalExtension()
    {
        return pathinfo($this->originalName, \PATHINFO_EXTENSION);
    }

    /**
     * Returns the file mime type.
     *
     * @return string The mime type
     */
    public function getClientMimeType()
    {
        return $this->mimeType;
    }

    /**
     * Returns the extension based on the client mime type.
     *
     * If the mime type is unknown, returns null.
     *
     * @return string|null The guessed extension or null if it cannot be guessed
     */
    public function guessClientExtension()
    {
        return $this->guessExtension();
    }

    /**
     * Returns the original file name.
     *
     * It is extracted from the request from which the file has been uploaded.
     * Then it should not be considered as a safe value.
     *
     * @return string The original name
     */
    public function getClientOriginalName()
    {
        return $this->originalName;
    }

    /**
     * Returns the upload error.
     *
     * If the upload was successful, the constant UPLOAD_ERR_OK is returned.
     * Otherwise one of the other UPLOAD_ERR_XXX constants is returned.
     *
     * @return int The upload error
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Returns whether the file was uploaded successfully.
     *
     * @return bool True if the file has been uploaded with HTTP and no error occurred
     */
    public function isValid()
    {
        $isOk = \UPLOAD_ERR_OK === $this->error;

        return $isOk && is_uploaded_file($this->getPathname());
    }

    /**
     * Moves the file to a new location.
     *
     * @return File A File object representing the new file
     *
     * @throws FileException if, for any reason, the file could not have been moved
     */
    public function move($directory, $name = null)
    {
        if ($this->isValid()) {
            $target = $this->getTargetFile($directory, $name);

            set_error_handler(function ($type, $msg) use (&$error) { $error = $msg; });
            $moved = move_uploaded_file($this->getPathname(), $target);
            restore_error_handler();
            if (!$moved) {
                throw new FileException(sprintf('Could not move the file "%s" to "%s" (%s).', $this->getPathname(), $target, strip_tags($error)));
            }

            @chmod($target, 0666 & ~umask());

            return new self($target->getPath(), $target->getPathname());
        }

        // switch ($this->error) {
        //     case \UPLOAD_ERR_INI_SIZE:
        //         throw new IniSizeFileException($this->getErrorMessage());
        //     case \UPLOAD_ERR_FORM_SIZE:
        //         throw new FormSizeFileException($this->getErrorMessage());
        //     case \UPLOAD_ERR_PARTIAL:
        //         throw new PartialFileException($this->getErrorMessage());
        //     case \UPLOAD_ERR_NO_FILE:
        //         throw new NoFileException($this->getErrorMessage());
        //     case \UPLOAD_ERR_CANT_WRITE:
        //         throw new CannotWriteFileException($this->getErrorMessage());
        //     case \UPLOAD_ERR_NO_TMP_DIR:
        //         throw new NoTmpDirFileException($this->getErrorMessage());
        //     case \UPLOAD_ERR_EXTENSION:
        //         throw new ExtensionFileException($this->getErrorMessage());
        // }

        throw new FileException($this->getErrorMessage());
    }

    /**
     * Returns the maximum size of an uploaded file as configured in php.ini.
     *
     * @return int|float The maximum size of an uploaded file in bytes (returns float if size > PHP_INT_MAX)
     */
    public static function getMaxFilesize()
    {
        $sizePostMax = self::parseFilesize(ini_get('post_max_size'));
        $sizeUploadMax = self::parseFilesize(ini_get('upload_max_filesize'));

        return min($sizePostMax ?: \PHP_INT_MAX, $sizeUploadMax ?: \PHP_INT_MAX);
    }

    /**
     * Returns the given size from an ini value in bytes.
     *
     * @return int|float Returns float if size > PHP_INT_MAX
     */
    private static function parseFilesize(string $size)
    {
        if ('' === $size) {
            return 0;
        }

        $size = strtolower($size);

        $max = ltrim($size, '+');
        if (str_starts_with($max, '0x')) {
            $max = \intval($max, 16);
        } elseif (str_starts_with($max, '0')) {
            $max = \intval($max, 8);
        } else {
            $max = (int) $max;
        }

        switch (substr($size, -1)) {
            case 't': $max *= 1024;
            // no break
            case 'g': $max *= 1024;
            // no break
            case 'm': $max *= 1024;
            // no break
            case 'k': $max *= 1024;
        }

        return $max;
    }

    /**
     * Returns an informative upload error message.
     *
     * @return string The error message regarding the specified error code
     */
    public function getErrorMessage()
    {
        static $errors = [
            \UPLOAD_ERR_INI_SIZE => 'The file "%s" exceeds your upload_max_filesize ini directive (limit is %d KiB).',
            \UPLOAD_ERR_FORM_SIZE => 'The file "%s" exceeds the upload limit defined in your form.',
            \UPLOAD_ERR_PARTIAL => 'The file "%s" was only partially uploaded.',
            \UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
            \UPLOAD_ERR_CANT_WRITE => 'The file "%s" could not be written on disk.',
            \UPLOAD_ERR_NO_TMP_DIR => 'File could not be uploaded: missing temporary directory.',
            \UPLOAD_ERR_EXTENSION => 'File upload was stopped by a PHP extension.',
        ];

        $errorCode = $this->error;
        $maxFilesize = \UPLOAD_ERR_INI_SIZE === $errorCode ? self::getMaxFilesize() / 1024 : 0;
        $message = isset($errors[$errorCode]) ? $errors[$errorCode] : 'The file "%s" was not uploaded due to an unknown error.';

        return sprintf($message, $this->getClientOriginalName(), $maxFilesize);
    }
}

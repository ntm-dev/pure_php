<?php

namespace Core\File;

use Core\File\MimeTypes;
use Core\File\UploadedFile;

/**
 * A file uploaded through a form.
 * 
 * @author Nguyen The Manh <nguyenthemanh26011996@gmail.com>
 */
class Upload
{
    private static $files;
    private static $allowExtensions;

    /**
     * Init upload file
     * 
     * @return void
     */
    private static function initUploadFile()
    {
        $listFile = [];
        foreach($_FILES as $inputName => $value) {
            foreach((array)$value['name'] as $key => $fileName) {
                $error = (array)$value['error'];
                $tmpName = (array)$value['tmp_name'];
                if (!$error[$key]) {
                    $file = new UploadedFile($tmpName[$key], $fileName, $error[$key]);
                    if (in_array($file->guessExtension(), self::getAllowExtensions())) {
                        $listFile[$inputName][] = $file;
                    }
                }
            }
        }
        self::$files = $listFile;
    }

    /**
     * Get files.
     * 
     * @return array[UploadedFile]
     */
    public static function get()
    {
        if (!self::$files) {
            self::initUploadFile();
        }

        return self::$files;
    }

    /**
     * Set allow file extensions.
     * 
     * @param array $allowExtensions
     * @return void
     */
    public static function setAllowExtensions($allowExtensions = [])
    {
        self::$allowExtensions = $allowExtensions ?: array_keys(MimeTypes::REVERSE_MAP);
    }

    /**
     * Get allow file extensions.
     * 
     * @return array
     */
    public static function getAllowExtensions()
    {
        if (!self::$allowExtensions) {
            self::setAllowExtensions();
        }

        return self::$allowExtensions;
    }
}

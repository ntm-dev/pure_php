<?php

namespace Support\File;

use Support\File\MimeTypes;
use Support\File\UploadedFile;

/**
 * A file uploaded through a form.
 * 
 * @author Nguyen The Manh <manh.nguyen3@ntq-solution.com.vn>
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
            foreach($value['name'] as $key => $fileName) {
                if (!$value['error'][$key]) {
                    $file = new UploadedFile($value['tmp_name'][$key], $fileName, $value['error'][$key]);
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

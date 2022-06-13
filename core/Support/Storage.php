<?php

namespace Core\Support;

class Storage
{
    public static function files($directory, $extension = "*.*")
    {
        return glob("$directory/$extension");
    }

    public static function allFiles($directory, $extension = "*.*")
    {
        $files = [];
        $fileAndDirectory = static::files($directory, "*");

        foreach ($fileAndDirectory as $value) {
            if (is_dir($value)) {
                $files += static::allFiles($value, $extension);
            } elseif ($extension == "*.*" || preg_match("/" . preg_quote($extension) . "$/", $value)) {
                $files[] = $value;
            }
        }

        return $files;
    }
}

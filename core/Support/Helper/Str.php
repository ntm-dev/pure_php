<?php

namespace Support\Helper;

/**
 * String helper.
 *
 * @author Nguyen The Manh <nguyenthemanh26011996@gmail.com>
 */
class Str
{
    /**
     * Get string length.
     *
     * @param  string $value
     * @param  bool   $multibyte
     * @return int
     */
    public static function length($value, $multibyte = true)
    {
        return $multibyte ? mb_strlen($value) : strlen($value);
    }

    /**
     * Trim string.
     *
     * @param  string $value
     * @param  bool   $fullsize
     * @return string
     */
    public static function trim($value, $fullsize = true)
    {
        if ($fullsize) {
            $value = self::rtrim(self::ltrim($value));
        }

        return trim($value);
    }

    /**
     * Left trim string.
     *
     * @param  string $value
     * @param  bool   $fullsize
     * @return string
     */
    public static function ltrim($value, $fullsize = true)
    {
        if ($fullsize) {
            $value = preg_replace('/^[\s]+/u', '', $value);
        }

        return trim($value);
    }

    /**
     * Right trim string.
     *
     * @param  string $value
     * @param  bool   $fullsize
     * @return string
     */
    public static function rtrim($value, $fullsize = true)
    {
        if ($fullsize) {
            $value = preg_replace('/[\s]+$/u', '', $value);
        }

        return trim($value);
    }

    /**
     * Uppercase to lowercase and lowercase to uppercase(ASCII string only).
     * 
     * @param  string $value
     * @return string
     */
    public static function invertCase($value)
    {
        return strtolower($value) ^ strtoupper($value) ^ $value;
    }

    /**
     * Return the remainder of a string after the first occurrence of a given value.
     *
     * @param  string  $subject — The string to search in
     * @param  string  $search — The string to search
     * @return string|false — the extracted part of string or false on failure.
     */
    public static function after($subject, $search)
    {
        $offset = self::strpos($subject, $search) + self::strlen($search);

        return self::substr($subject, $offset);
    }

    /**
     * Return the remainder of a string after the last occurrence of a given value.
     *
     * @param  string  $subject — The string to search in
     * @param  string  $search — The string to search
     * @return string|false — the extracted part of string or false on failure.
     */
    public static function afterLast($subject, $search)
    {
        $offset = self::strrpos($subject, $search) + self::strlen($search);

        return self::substr($subject, $offset);
    }

    /**
     * Get the portion of a string before the first occurrence of a given value.
     *
     * @param  string  $subject — The string to search in
     * @param  string  $search — The string to search
     * @return string|false — the extracted part of string or false on failure.
     */
    public static function before($subject, $search)
    {
        return self::substr($subject, 0, self::strpos($subject, $search));
    }

    /**
     * Returns everything before the last occurrence of the given value in a string.
     * 
     * @param  string $subject — The string to search in
     * @param  string $needle — The string to search
     * @return string|false — the extracted part of string or false on failure.
     */
    public static function beforeLast($subject, $search)
    {
        return self::substr($subject, 0, self::strrpos($subject, $search));
    }

    /**
     * Convert a value to camel case.
     *
     * @param  string  $value
     * @return string
     */
    public static function camel($value)
    {
        return lcfirst(static::studly($value));
    }


    /**
     * Convert a value to studly caps case.
     *
     * @param  string  $value
     * @return string
     */
    public static function studly($value)
    {
        $words = explode(' ', str_replace(['-', '_'], ' ', $value));

        $studlyWords = array_map(function ($word) {
            return ucfirst($word);
        }, $words);

        return implode($studlyWords);
    }

    /**
     * Convert the given string to title case.
     *
     * @param  string  $value
     * @return string
     */
    public static function title($value)
    {
        return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Split a string into pieces by uppercase characters.
     *
     * @param  string  $string
     * @return array
     */
    public static function ucsplit($string)
    {
        return preg_split('/(?=\p{Lu})/u', $string, -1, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * Convert the given string to title case for each word.
     *
     * @param  string  $value
     * @return string
     */
    public static function headline($value)
    {
        $parts = explode(' ', $value);

        $parts = count($parts) > 1
            ? $parts = array_map([static::class, 'title'], $parts)
            : $parts = array_map([static::class, 'title'], static::ucsplit(implode('_', $parts)));

        $collapsed = str_replace(['-', '_', ' '], '_', implode('_', $parts));

        return implode(' ', array_filter(explode('_', $collapsed)));
    }

    /**
     * Convert a string to snake case.
     *
     * @param  string  $value
     * @param  string  $delimiter
     * @return string
     */
    public static function snake($value, $delimiter = '_')
    {
        if (! ctype_lower($value)) {
            $value = preg_replace('/\s+/u', '', ucwords($value));

            $value = static::lower(preg_replace('/(.)(?=[A-Z])/u', '$1'.$delimiter, $value));
        }

        return $value;
    }

    /**
     * Convert a string to kebab case.
     *
     * @param  string  $value
     * @return string
     */
    public static function kebab($value)
    {
        return static::snake($value, '-');
    }

    /**
     * Generate a URL friendly "slug" from a given string.
     *
     * @param  string  $title
     * @param  string  $separator
     * @return string
     */
    public static function slug($title, $separator = '-')
    {
        // Convert all dashes/underscores into separator
        $flip = $separator === '-' ? '_' : '-';

        $title = preg_replace('/['.preg_quote($flip).']+/u', $separator, $title);

        // Replace @ with the word 'at'
        $title = str_replace('@', $separator.'at'.$separator, $title);

        // Remove all characters that are not the separator, letters, numbers, or whitespace.
        $title = preg_replace('/[^'.preg_quote($separator).'\pL\pN\s]+/u', '', strtolower($title));

        // Replace all separator characters and whitespace by a single separator
        $title = preg_replace('!['.preg_quote($separator).'\s]+!u', $separator, $title);

        return trim($title, $separator);
    }

    /**
     * Is triggered when invoking inaccessible methods in a static context.
     * 
     * @param  string $name
     * @param  mixed  $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        if (function_exists("mb_$name")) {
            return call_user_func_array("mb_$name", $arguments);
        }
        if (function_exists($name)) {
            return call_user_func_array($name, $arguments);
        }
    }
}

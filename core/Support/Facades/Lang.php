<?php

namespace Core\Support\Facades;

use Core\Support\Facades\Facade;

/**
 * Support Language Facade.
 * @experimental
 *
 * @author Nguyen The Manh <nguyenthemanh26011996@gmail.com>
 *
 * @method static string getLocale() Get locale
 * @method static \Core\Translation\Translator setLocale(string $locale) Set locale
 * @method static string get(string $key, array $replace = [], string|null $locale = null) Get the translation for the given key.
 * @method static bool load(string $file) Load the specified language file.
 * @method static \Core\Translation\Translator directory(string $directory) Set default directory of translator.
 * @method static string getDirectory() Get directory of translator
 */
class Lang extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \Core\Translation\Translator::class;
    }
}

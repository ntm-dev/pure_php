<?php

namespace Core\Translation;

use Core\Pattern\Singleton;
use Core\Support\Helper\Arr;
use Core\Support\Helper\Str;

/**
 * Translator class.
 *
 * @author Nguyen The Manh <manh.nguyen3@ntq-solution.com.vn>
 */
class Translator
{
    use Singleton;

    const DEFAULT_MESSAGE_FILE = "messages";

    /**
    * The default locale being used by the translator.
    *
    * @var string
    */
    public string $locale;

    /**
    * The default directory of translator.
    *
    * @var string
    */
    public ?string $directory = '';

    /** @var array */
    protected array $loaded = [];

    /** @var array */
    protected $loadedFile = [];

    public function __construct(string $locale = null)
    {
        self::setInstance($this);
        $locale = $locale ?: config("app.locale");
        $this->setLocale($locale);
    }

    /**
     * Get locale.
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * Set locale.
     */
    public function setLocale(string $locale): static
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Get the translation for the given key.
     */
    public function get(string $key, array $replace = [], string|null $locale = null): string|array
    {
        $locale ? ($this->locale = $locale) : $this->locale;

        $this->load($this->messageFile(Str::before($key, ".")));

        return $this->replaceAttribute(Arr::get($this->loaded, "{$this->locale}.{$key}", ''), $replace)
            ?: $this->getFromDefaultFile($key, $replace, $this->locale);
    }

    /**
     * Load the specified language file.
     */
    public function load(string $file, string $prefix = ''): bool
    {
        if (in_array($file, $this->loadedFile)) {
            return true;
        }

        $prefix = $prefix ?: Str::before(
            Str::replace(DIRECTORY_SEPARATOR, ".", Str::after($file, $this->locale . DIRECTORY_SEPARATOR)),
            ".php"
        );
        if (file_exists($file) && is_array($data = @include $file)) {
            $this->loadedFile[] = $file;
            $this->loaded[$this->locale][$prefix] = $data;
            return true;
        }

        return false;
    }

    /**
     * Set default directory of translator
     */
    public function directory(string $directory): static
    {
        $this->directory = $directory;

        return $this;
    }

    /**
     * Get directory of translator
     */
    public function getDirectory(): string
    {
        return $this->directory ?: base_path('lang');
    }

    /**
     * Return message file by set locale.
     */
    private function messageFile(string $fileName): string
    {
        return implode(DIRECTORY_SEPARATOR, [
            $this->getDirectory(),
            $this->locale,
            $fileName,
        ]) . ".php";
    }


    /**
     * Replace message by attribute.
     */
    private function replaceAttribute(string $message, array $replace = []): string
    {
        if (Str::isEmpty($message, false)) {
            return $message;
        }

        foreach ($replace as $searchKey => $replaceValue) {
            $message = Str::replace(":{$searchKey}", $replaceValue, $message);
            if (Str::contains($message, ":" . ucfirst($searchKey))) {
                $message = Str::replace(
                    ":" . ucfirst($searchKey),
                    ucfirst($replaceValue),
                    $message);
            }
        }

        return $message;
    }

    /**
     * Get message from default message file.
     */
    private function getFromDefaultFile(string $key, array $replace = [], string|null $locale = null): string
    {
        return $this->replaceAttribute(
            Arr::get(
                $this->loaded,
                sprintf("{$locale}.%s.{$key}", static::DEFAULT_MESSAGE_FILE)
            ),
            $replace
        );
    }
}

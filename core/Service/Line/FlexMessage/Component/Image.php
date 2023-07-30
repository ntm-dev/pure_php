<?php

namespace Core\Service\Line\FlexMessage\Component;

use Core\Support\Helper\Str;
use UnexpectedValueException;
use Core\Service\Line\FlexMessage\Component\FlexTrait;
use Core\Service\Line\FlexMessage\Component\ColorTrait;
use Core\Service\Line\FlexMessage\Component\MarginTrait;

class Image implements BoxContentInterface
{
    use FlexTrait, ColorTrait, MarginTrait;

    public const URL_MAX_LENGTH = 2000;
    public const ALLOW_SIZES = ['sm', 'md', 'lg', 'xs', 'xl', 'xxs', 'xxl', '3xl', '4xl', '5xl', 'full'];
    private const TYPE = 'image';

    /**
     * Image url
     *
     * @var string
     */
    private string $url;

    /**
     * Image size
     *
     * @var string
     */
    protected string $size = 'md';

    public function url(string $url)
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new UnexpectedValueException('Argument #1 ($url) must be a valid url');
        }

        if (strlen($url) > self::URL_MAX_LENGTH) {
            throw new UnexpectedValueException(sprintf('Argument #1 ($url) cannot be longer than %d characters', self::URL_MAX_LENGTH));
        }

        $this->url = $url;

        return $this;
    }

    /**
     * Set font size
     *
     * @param  string  $text
     * @return $this
     */
    public function size(string $size)
    {
        if (!in_array($size, self::ALLOW_SIZES)) {
            if (!Str::endsWith($size, 'px')
            || !is_numeric(Str::before($size, 'px'))
            || Str::before($size, 'px') < "0") {
                throw new UnexpectedValueException('Argument #1 ($size) must be a positive integer or decimal number that ends in px. Examples include 50px and 23.5px');
            }
            $percentage  = Str::before($size, '%');
            if (!Str::endsWith($size, '%')
            || !is_numeric($percentage)
            || ($percentage < 0  && $percentage > 100)) {
                throw new UnexpectedValueException('Argument #1 ($size) must be expressed as a positive integer or decimal number with %. Examples include 50% and 23.5%');
            }
        }

        $this->size = $size;

        return $this;
    }

    public function toArray(): array
    {
        return array_merge(["type" => self::TYPE], get_object_vars($this));
    }
}

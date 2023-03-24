<?php

namespace Core\Service\Line\FlexMessage\Component;

use Core\Support\Helper\Str;
use UnexpectedValueException;
use Core\Service\Line\FlexMessage\Enum\FontSize;

class Text implements BoxContentInterface
{
    private const TYPE = 'text';

    protected string $text;

    protected FontSize|string $size;

    public function text(string $text)
    {
        if (Str::isEmpty($text)) {
            throw new UnexpectedValueException('Argument #1 ($text) can not be empty');
        }
        $this->text = $text;

        return $this;
    }

    public function size(FontSize|string $size)
    {
        if (is_string($size)) {
            if (
                !in_array($size, array_column(FontSize::cases(), 'value'))
                && (!Str::endsWith($size, 'px')
                || !is_numeric(Str::before($size, 'px'))
                || Str::before($size, 'px') < "0")
            ) {
                throw new UnexpectedValueException('Argument #1 ($size) must A positive integer or decimal number that ends in px. Examples include 50px and 23.5px');
            }
        }
        $this->size = $size;

        return $this;
    }

    public function toArray(): array
    {
        return [
            "type" => self::TYPE,
            "text" => $this->text,
            "size" => $this->size,
            "weight" => "bold",
            "color" => "#0000ff"
        ];
    }
}

<?php

namespace Core\Service\Line\FlexMessage\Component;

use Core\Contract\ArrayAble;
use UnexpectedValueException;

class BlockStyle implements ArrayAble
{
    protected bool $separator = false;

    /**
     * Background color of the block
     *
     * @var string
     */
    protected ?string $backgroundColor;

    /**
     * Color of the separator.
     *
     * @var string
     */
    protected ?string $separatorColor;

    /**
     * Show separator
     *
     * @param  bool  $show
     * @return $this
     */
    public function separator(bool $show)
    {
        $this->separator = $show;

        return $this;
    }

    /**
     * Set font color
     *
     * @param  string  $color
     * @return $this
     */
    public function backgroundColor(string $backgroundColor)
    {
        if (!preg_match('/^#([A-Fa-f0-9]{6})$/', $backgroundColor)) {
            throw new UnexpectedValueException('Argument #1 ($backgroundColor) must be a valid hexadecimal color code');
        }

        $this->backgroundColor = $backgroundColor;

        return $this;
    }

    /**
     * Set font color
     *
     * @param  string  $color
     * @return $this
     */
    public function separatorColor(string $separatorColor)
    {
        if (!preg_match('/^#([A-Fa-f0-9]{6})$/', $separatorColor)) {
            throw new UnexpectedValueException('Argument #1 ($separatorColor) must be a valid hexadecimal color code');
        }

        $this->separatorColor = $separatorColor;

        return $this;
    }

    public function toArray(): array
    {
        $value = [];

        if (isset($this->backgroundColor)) {
            $value['backgroundColor'] = $this->backgroundColor;
        }

        if ($this->separator) {
            $value['separator'] = $this->separator;
            if (isset($this->separatorColor)) {
                $value['separatorColor'] = $this->separatorColor;
            }
        }

        return $value;
    }

}

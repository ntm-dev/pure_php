<?php

namespace Core\Service\Line\FlexMessage\Bubble;

use Core\Contract\ArrayAble;
use Core\Service\Line\FlexMessage\Enum\BubbleSize;
use Core\Service\Line\FlexMessage\Component\Box;
use Core\Service\Line\FlexMessage\Enum\Direction;

class Message implements ArrayAble
{
    private const TYPE = 'bubble';

    protected BubbleSize $size = BubbleSize::Mega;
    protected Direction $direction = Direction::LeftToRight;

    public Box $header;
    public Box $body;
    public Box $footer;
    public Style $style;

    public function __construct(Box $header, Box $body, Box $footer, Style $style)
    {
        $this->header = $header;
        $this->body = $body;
        $this->footer = $footer;
        $this->style = $style;
    }

    public function size(BubbleSize $size)
    {
        $this->size = $size;

        return $this;
    }

    public function direction(Direction $direction)
    {
        $this->direction = $direction;

        return $this;
    }

    public function toJson()
    {
        return json_encode($this->toArray());
    }

    public function __toString(): string
    {
        return $this->toJson();
    }

    public function toArray(): array
    {
        $value = [
            'type' => self::TYPE,
            'size' => $this->size,
            'body' => $this->body->toArray(),
            'footer' => $this->footer->toArray(),
            'styles' => $this->style->toArray(),
        ];

        return array_filter($value, function($v) { return !empty($v);});
    }
}

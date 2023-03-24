<?php

namespace Core\Service\Line\FlexMessage\Bubble;

use Core\Support\Helper\Str;
use UnexpectedValueException;
use Core\Service\Line\FlexMessage\Enum\BubbleSize;
use Core\Service\Line\FlexMessage\Component\Box;
use Core\Service\Line\FlexMessage\Enum\Direction;

class Message
{
    private const TYPE = 'bubble';

    protected BubbleSize $size = BubbleSize::Mega;
    protected Direction $direction = Direction::LeftToRight;

    public string $altText;
    public Box $header;
    public Box $body;
    public Box $footer;

    public function __construct(Box $header, Box $body, Box $footer)
    {
        $this->header = $header;
        $this->body = $body;
        $this->footer = $footer;
    
    }

    public function altText(string $altText)
    {
        if (Str::isEmpty($altText)) {
            throw new UnexpectedValueException('Argument #1 ($altText) can not be empty');
        }

        return $this;
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

    public function body(Box $body)
    {
        $this->body = $body;

        return $this;
    }

    public function toJson()
    {
        return json_encode($this->toArray());
    }

    public function toArray()
    {
        return [
            'type' => self::TYPE,
            'size' => $this->size,
            'body' => $this->body->toArray(),
        ];
    }
}

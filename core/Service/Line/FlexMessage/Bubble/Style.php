<?php

namespace Core\Service\Line\FlexMessage\Bubble;

use Core\Contract\ArrayAble;
use Core\Service\Line\FlexMessage\Component\BlockStyle;

class Style implements ArrayAble
{
    public BlockStyle $header;
    public BlockStyle $hero;
    public BlockStyle $body;
    public BlockStyle $footer;

    public function __construct(BlockStyle $header, BlockStyle $hero, BlockStyle $body, BlockStyle $footer)
    {
        $this->header = $header;
        $this->hero = $hero;
        $this->body = $body;
        $this->footer = $footer;
    }

    public function toArray(): array
    {
        $value = [];
        foreach (get_object_vars($this) as $k => $v) {
            $data = $v->toArray();
            if (!empty($data)) {
                $value[$k] = $data;
            }
        }

        return $value;
    }
}

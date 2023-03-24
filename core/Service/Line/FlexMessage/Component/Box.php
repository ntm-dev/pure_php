<?php

namespace Core\Service\Line\FlexMessage\Component;

use Core\Service\Line\FlexMessage\LayoutType;

class Box
{
    private const TYPE = 'box';

    protected LayoutType $layout = LayoutType::Vertical;

    protected array $contents;

    public function setContent(BoxContentInterface $contents)
    {
        $this->contents[] = $contents;

        return $this;
    }

    public function toJson()
    {

    }

    public function toArray()
    {
        return [
            "type" => self::TYPE,
            "layout" => $this->layout,
            "contents" => array_map(function($content) {
                return $content->toArray();
            }, $this->contents)
        ];
    }
}

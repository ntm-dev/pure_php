<?php

namespace Core\Service\Line\FlexMessage\Component;

use Core\Service\Line\FlexMessage\Component\ColorTrait;
use Core\Service\Line\FlexMessage\Component\MarginTrait;

class Separator implements BoxContentInterface
{
    use ColorTrait, MarginTrait;

    private const TYPE = 'separator';

    public function toArray(): array
    {
        return array_merge(["type" => self::TYPE], get_object_vars($this));
    }
}

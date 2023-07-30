<?php

namespace Core\Contract;

interface StringAble
{
    /**
     * Convert the object to string.
     *
     * @return string
     */
    public function __toString(): string;
}

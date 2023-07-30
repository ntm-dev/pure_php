<?php

namespace Core\Contract;

/**
 * @template TKey of array-key
 * @template TValue
 */
interface ArrayAble
{
    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray(): array;
}

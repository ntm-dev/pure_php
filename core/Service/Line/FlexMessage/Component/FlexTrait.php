<?php

namespace Core\Service\Line\FlexMessage\Component;

use UnexpectedValueException;

trait FlexTrait
{
    /**
     * The ratio of the width or height of this component within the parent box.
     *
     * @var int
     */
    protected int $flex;

    /**
     * Set ratio of the width or height of this component within the parent box.
     *
     * @param  int  $ratio
     * @return $this
     */
    public function flex(int $ratio)
    {
        if (0 > $ratio) {
            throw new UnexpectedValueException(sprintf('%s: Argument #1 ($ratio) must be greater than or equal to 0', __METHOD__));
        }

        $this->flex = $ratio;

        return $this;
    }
}

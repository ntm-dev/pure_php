<?php

namespace Core\Service\Line\FlexMessage\Component;

use Core\Support\Helper\Str;
use UnexpectedValueException;
use Core\Service\Line\FlexMessage\Enum\Margin;

trait MarginTrait
{
    /**
     * Margin. The minimum amount of space to include before this component in its parent container.
     *
     * @var \Core\Service\Line\FlexMessage\Enum\Margin|string
     */
    protected Margin|string $margin;

    public function margin(Margin|string $margin)
    {
        if (is_string($margin)) {
            if (
                !in_array($margin, array_column(Margin::cases(), 'value'))
                && (!Str::endsWith($margin, 'px')
                    || !is_numeric(Str::before($margin, 'px'))
                    || Str::before($margin, 'px') < "0")
            ) {
                throw new UnexpectedValueException('Argument #1 ($margin) must be a positive integer or decimal number that ends in px. Examples include 50px and 23.5px');
            }
        }

        $this->margin = $margin;

        return $this;
    }
}

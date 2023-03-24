<?php

namespace Core\Service\Line\FlexMessage\Enum;

/**
 * Text directionality and the direction of placement of components in horizontal boxes.
 */
enum Direction: string
{
    case LeftToRight = 'ltr'; // The text is left-to-right horizontal writing, and the components are placed from left to right
    case RightToLeft = 'rtl'; // The text is right-to-left horizontal writing, and the components are placed from right to left
}

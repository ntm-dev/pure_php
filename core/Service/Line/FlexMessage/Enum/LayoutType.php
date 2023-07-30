<?php

namespace Core\Service\Line\FlexMessage\Enum;

/**
 *The direction of the main axis is determined by a box's type. Specify the type of any box through its layout property.
 */
enum LayoutType: string
{
    case Vertical = 'vertical'; // The main axis is vertical. Child elements are arranged vertically from top to bottom. The cross axis is horizontal.
    case Baseline = 'baseline'; //T he main axis runs in the same direction as a horizontal box
    case Horizontal = 'horizontal'; // The main axis is horizontal. Child elements are arranged horizontally. The cross axis is vertical.
}

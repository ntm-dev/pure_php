<?php

namespace Core\Views;

use Core\Contract\AbstractProperty;
abstract class ViewAbstract extends AbstractProperty
{
    protected const REQUIRE_CONSTANTS = ['TEMPLATE_NAME', 'TEMPLATE_EXTENSION', 'TEMPLATE_DIR'];
    protected const REQUIRE_PROPERTIES = ['template', 'view'];
}

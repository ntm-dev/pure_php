<?php

namespace Core\Views;

use Exception;
use ReflectionProperty;
use ReflectionClassConstant;

abstract class ViewAbstract
{
    private const REQUIRE_CONSTANTS = ['TEMPLATE_NAME', 'TEMPLATE_EXTENSION', 'TEMPLATE_DIR'];
    private const REQUIRE_PROPERTIES = ['template', 'view'];

    public function __construct(string $template = '')
    {
        $this->checkRequireConstant();
        $this->checkRequireProperty();
    }

    private function checkRequireConstant()
    {
        foreach (self::REQUIRE_CONSTANTS as $const) {
            try {
                new ReflectionClassConstant(static::class, $const);
            } catch (\Throwable $th) {
                throw new Exception("Class \"" . static::class . "\" must conttain \"$const\" constant.");
            }
        }
    }

    private function checkRequireProperty()
    {
        foreach (self::REQUIRE_PROPERTIES as $property) {
            try {
                new ReflectionProperty(static::class, $property);
            } catch (\Throwable $th) {
                throw new Exception("Class \"" . static::class . "\" must conttain \"$property\" property.");
            }
        }
    }
}

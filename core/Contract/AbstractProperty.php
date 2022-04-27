<?php

namespace Core\Contract;

use Exception;
use ReflectionProperty;
use ReflectionClassConstant;

class AbstractProperty
{
    protected const REQUIRE_CONSTANTS = [];
    protected const REQUIRE_PROPERTIES = [];

    public function __construct()
    {
        $this->checkRequireConstant();
        $this->checkRequireProperty();
    }

    private function checkRequireConstant()
    {
        foreach (static::REQUIRE_CONSTANTS as $const) {
            try {
                new ReflectionClassConstant(static::class, $const);
            } catch (\Throwable $th) {
                throw new Exception("Class \"" . static::class . "\" must conttain \"$const\" constant.");
            }
        }
    }

    private function checkRequireProperty()
    {
        foreach (static::REQUIRE_PROPERTIES as $property) {
            try {
                new ReflectionProperty(static::class, $property);
            } catch (\Throwable $th) {
                throw new Exception("Class \"" . static::class . "\" must conttain \"$property\" property.");
            }
        }
    }
}

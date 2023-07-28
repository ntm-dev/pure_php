<?php

namespace Core\Console;

use Core\Support\Helper\Arr;

/**
 * Command grammar class.
 *
 * @author Nguyen The Manh <manh.nguyen3@ntq-solution.com.vn>
 */
class Grammar
{
    /** @var array */
    private $input;

    public function __construct(array $argv = null)
    {
        $argv = $argv ?: (isset($_SERVER['argv']) ? $_SERVER['argv'] : []);

        // strip the application name
        array_shift($argv);

        $this->input = $argv;
    }

    public function getFirstOption()
    {
        return Arr::first($this->input);
    }

    public function getOptions()
    {
        return $this->input;
    }

    public function getCommand()
    {
        $options = $this->getOptions();
        $firstOption = Arr::pull($options, 0);
        $firstOption = explode(":", $firstOption);

        return [
            'method' => $firstOption[0],
            'target' => isset($firstOption[1]) ? $firstOption[1] : null,
            'arguments' => $options,
        ];
    }

}

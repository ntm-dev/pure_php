<?php

namespace Core\Console;

use Core\Console\Command;
use Core\Console\Grammar;
use Core\Support\Helper\Str;
use Core\Console\ColorFormat;

/**
 * Command kernel class.
 *
 * @author Nguyen The Manh <manh.nguyen3@ntq-solution.com.vn>
 */
class Kernel
{
    /** @var \Core\Console\Grammar */
    protected $grammar;

    /** @var \Core\Console\Command */
    protected $command;

    /** @var array */
    protected static $commandMap = [];

    public function __construct()
    {
        $this->grammar = app()->make(Grammar::class, true);
        $this->command = app()->make(Command::class, true);
    }

    public function run()
    {
        $command = $this->grammar->getCommand();

        if (empty($command['method'])) {
            return $this->command->bgRed("Command method can not be empty.");
        }

        $token = $command['method'].($command['target'] ? ":{$command['target']}" : "");
        if (empty($command['method']) || !in_array($token, array_keys($this->commandMapping()))) {
            $output = sprintf("Command \"%s\" is not defined.", $token);
            if (!empty($suggestions = $this->suggestCommand($token))) {
                $output .= "\n\nDid you mean:\n\t" . implode("\n\t", $suggestions);
            }

            return $this->command->bgRed($output);
        }

        set_error_handler([$this, 'setErrorHandler']);

        return $this->runCommand($command);
    }

    public function setErrorHandler()
    {
        list ( $errno, $errstr, $errFile, $errLine, $errContext) = func_get_args();

        dd(get_defined_vars());
    }

    protected function suggestCommand($input)
    {
        $suggestions = [];

        foreach (array_keys($this->commandMapping()) as $value) {
            if (Str::contains($value, $input)) {
                $suggestions[] = $value;
            }
        }

        return $suggestions;
    }

    protected function runCommand($command)
    {
        $commandMap = $this->commandMapping();
        $classMap = $commandMap[$command['method'].($command['target'] ? ":{$command['target']}" : "")];
        $commandInstance = app()->make($classMap['class'], true);

        error_reporting(0);
        try {
            $result = $commandInstance->{$classMap['method']}(...$command['arguments']);
        } catch (\Exception $e) {
            dd($e);
        }

        if (isset($classMap['callback'])) {
            $classMap['callback']($result);
        }

        return $result;
    }

    protected function commandMapping()
    {
        if (self::$commandMap) {
            return self::$commandMap;
        }

        return self::$commandMap = [
        ];
    }

    private function underscoreText($text)
    {
        return rtrim(rtrim($this->command->underscore($text, false), "\n"), ColorFormat::ESCAPE);
    }

}

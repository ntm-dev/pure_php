<?php

namespace Core\Console;

use RuntimeException;
use ReflectionMethod;
use Core\Console\Command;
use Core\Console\Grammar;
use Core\Support\Helper\Str;
use Core\Console\ColorFormat;
use Core\Contract\CommandExecutor;
use Core\Database\Migrations\CommandMigrator;
use Core\Database\Migrations\MigrationCreator;

/**
 * Command kernel class.
 *
 * @author Nguyen The Manh <nguyenthemanh26011996@gmail.com>
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
        $this->grammar = container(Grammar::class, true);
        $this->command = container(Command::class, true);
    }

    /**
     * Run command
     */
    public function run()
    {
        $input = $this->grammar->getInput();

        set_error_handler([$this, 'errorHandler']);
        set_exception_handler([$this, 'exceptionHandler']);

        $executor = $this->getCommandExecutor($input);
        $classMap = $this->getClassMap($input);

        return $this->doRun($executor, $classMap, $input);
    }

    /**
     * Do run
     *
     * @param  \Core\Contract\CommandExecutor|null $executor
     * @param  array $classMap
     * @param  array $input
     */
    private function doRun($executor, $classMap, $input)
    {
        error_reporting(0);

        $this->validate($executor, $classMap, $input);

        return $this->runCommand($executor, $classMap['method'], $input['arguments'], $classMap["callback"] ?? null);
    }

    /**
     * Validate command
     *
     * @param  \Core\Contract\CommandExecutor|null $executor
     * @param  array $classMap
     * @param  array $input
     * @return void
     * @throws \RuntimeException
     */
    private function validate($executor, $classMap, $input)
    {
        if (empty($input['method'])) {
            return $this->command->bgRed("Command method can not be empty.");
        }

        $token = $input['method'].($input['target'] ? ":{$input['target']}" : "");
        if (empty($input['method']) || !in_array($token, array_keys($this->commandMapping()))) {
            $output = sprintf("Command \"%s\" is not defined.", $token);
            if (!empty($suggestions = $this->suggestCommand($token))) {
                $output .= "\n\nDid you mean:\n\t" . implode("\n\t", $suggestions);
            }

            $this->command->bgRed($output);
            exit();
        }

        $this->validateArgument($executor, $classMap['method'], $input['arguments']);
    }

    /**
     * Validate command argument
     *
     * @param  \Core\Contract\CommandExecutor|null $executor
     * @param  array $classMap
     * @param  array $input
     * @return void
     * @throws \RuntimeException
     */
    private function validateArgument($executor, $method, $arguments)
    {
        $requireParams = $this->getRequireParameter($executor, $method);

        foreach ($requireParams as $key => $param) {
            if (!$param->isDefaultValueAvailable() && !isset($arguments[$key])) {
                throw new RuntimeException(
                    sprintf(
                        'Not enough arguments (missing: "%s")',
                        $param->getName()
                    )
                );
            }
        }
    }

    /**
     * Get require parameter for command executor
     *
     * @param  \Core\Contract\CommandExecutor $executor
     * @param  string $method
     * @return \ReflectionParameter
     */
    private function getRequireParameter(CommandExecutor $executor, string $method)
    {
        return (new ReflectionMethod($executor, $method))->getParameters();
    }

    /**
     * Get class map
     *
     * @param  array $input
     * @return string|false
     */
    private function getClassMap($input)
    {
        $commandMap = $this->commandMapping();

        return $commandMap[$input['method'].($input['target'] ? ":{$input['target']}" : "")] ?? false;
    }

    /**
     * Get command executor
     *
     * @param  array $input
     * @return object|null
     */
    private function getCommandExecutor($input)
    {
        $class = $this->getClassMap($input);
        if (!$class) {
            return null;
        }

        return container($class, true);
    }

    /**
     * Handle error
     */
    public function errorHandler()
    {
        list ( $errno, $errstr, $errFile, $errLine, $errContext) = func_get_args();

        dd(get_defined_vars());
    }

    /**
     * Handle exception
     *
     * @param  \Throwable $exception
     */
    public function exceptionHandler(\Throwable $exception)
    {
        $this->command->error($exception->getMessage());
    }

    /**
     * get suggest command
     *
     * @param  array  $input
     * @return array
     */
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

    /**
     * Run command
     *
     * @param  \Core\Contract\CommandExecutor|null $executor
     * @param  string   $method
     * @param  array    $arguments
     * @param  callable $callback
     * @return mixed
     * @throws \Exception
     */
    protected function runCommand(CommandExecutor $executor, $method, $arguments, callable $callback = null)
    {
        $result = $executor->{$method}(...$arguments);

        if (!is_null($callback)) {
            $callback($result);
        }

        return $result;
    }

    protected function commandMapping()
    {
        if (self::$commandMap) {
            return self::$commandMap;
        }

        return self::$commandMap = [
            'make:migration' => [
                'class' => MigrationCreator::class,
                'method' => 'create',
                'callback' => function($filename) {
                    $underscoreText = $this->underscoreText($filename);

                    if (false === $filename) {
                        return $this->command->textRed("Unable to create migration {$underscoreText}");
                    }

                    return $this->command->textGreen("Created {$underscoreText}");
                }
            ],
            'migrate' => [
                'class' => CommandMigrator::class,
                'method' => 'migrate',
            ],
            'migrate:rollback' => [
                'class' => CommandMigrator::class,
                'method' => 'rollback',
            ],
        ];
    }

    private function underscoreText($text)
    {
        return rtrim(rtrim($this->command->underscore($text, false), "\n"), ColorFormat::ESCAPE);
    }

}

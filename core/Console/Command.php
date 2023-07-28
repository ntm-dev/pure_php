<?php

namespace Core\Console;

use BadMethodCallException;
use Core\Console\Formater;
use Core\Console\ProgressBar;
use RuntimeException;
use Core\Support\Helper\Str;

/**
 * Command class.
 *
 * @author Nguyen The Manh <nguyenthemanh26011996@gmail.com>
 */
class Command
{
    /**
     * @var \Core\Console\ProgressBar
     */
    public ProgressBar $progressBar ;

    /**
     * Create a question.
     *
     * @param  string $question
     * @return string|false
     */
    public function ask(string $question)
    {
        $question = $this->applyStyle("{$question}", 'green');
        $question = "\n$question:\n> ";

        return readline($question);
    }

    /**
     * Create a question and hide answer.
     *
     * @param  string $question
     * @return string|false
     */
    public function askHidden(string $question)
    {
        $question = $this->applyStyle("{$question}", 'green');
        echo "\n$question:\n> ";

        system('stty -echo'); //sets the echo property to false, so the terminal will NOT echo the input it receives to the screen, rendering the password invisible

        $input = trim(fgets(STDIN)); //retrieves input from a stream interface (STDIN).

        system('stty echo'); //restores the echo property to true, so we can now see output on the screen again

        return $input;
    }

    /**
     * Create a yes/no question.
     *
     * @param  string $question
     * @return bool
     */
    public function confirm(string $question)
    {
        $question = $this->applyStyle("{$question} (yes/no)", 'green');

        $input = readline("\n$question:\n> ");
        if (preg_match("/^[y|Y]/", $input)) {
            return true;
        }

        return false;
    }

    /**
     * Apply style for text.
     *
     * @param  string $text
     * @param  string $foreground
     * @param  string $background
     * @param  array  $options
     *
     * @return string
     */
    private function applyStyle(string $text, string $foreground = '', string $background = '', array $options = [])
    {
        return (new Formater($foreground, $background, $options))->apply($text);
    }

    /**
     * Create a question with options.
     *
     * @param  string $question
     * @param  array  $options
     * @return mixed
     */
    public function choise(string $question, array $options)
    {
        $question = $this->applyStyle($question, 'green');
        $optionStr = '';

        foreach ($options as $key => $option) {
            $key = $this->applyStyle($key, 'yellow');
            $optionStr .= " [{$key}] $option\n";
        }

        $question = $optionStr ? "{$question}: \n$optionStr" : $question;

        $choise = readline($this->applyStyle("\n{$question}> ", 'green'));

        if (!in_array($choise, array_keys($options))) {
            return '';
        }

        return $options[$choise];
    }

    /**
     * Show message with success status.
     *
     * @param  string $question
     * @return string
     */
    public function success(string $message)
    {
        return $this->showMessage("  [OK] $message", 'black', 'green');
    }

    /**
     * Show message with warn status.
     *
     * @param  string $question
     * @return string
     */
    public function warn(string $message)
    {
        return $this->showMessage($message, 'yellow');
    }

    /**
     * Show message with error status.
     *
     * @param  string $question
     * @return string
     */
    public function error(string $message)
    {
        return $this->showMessage("  [ERROR] $message", '', 'red');
    }

    /**
     * Show message with info status.
     *
     * @param  string $question
     * @return string
     */
    public function info(string $message)
    {
        return $this->showMessage("  [INFO] $message", 'cyan');
    }

    /**
     * Create and show a progress bar.
     *
     * @param  int $max
     * @return void
     */
    public function progressStart(int $max)
    {
        $this->progressBar = new ProgressBar($max);

        $this->progressBar->start();
    }

    /**
     * Finish progress bar.
     *
     * @return void
     */
    public function progressFinish()
    {
        $this->isExistProgressBar();

        return $this->progressBar->finish();
    }

    /**
     * Check exist progress bar.
     *
     * @return RuntimeException|true
     */
    private function isExistPogressBar()
    {
        if (empty($this->progressBar)) {
            throw new RuntimeException("Please call progressStart method to create progress bar first");
        }

        return true;
    }

    /**
     * Advance progress bar.
     *
     * @param  int $step
     * @return void
     */
    public function progressAdvance(int $step = 1)
    {
        $this->isExistPogressBar();

        return $this->progressBar->advance($step);
    }

    /**
     * Show message to terminal.
     *
     * @param  string $message
     * @param  string $foreground
     * @param  string $background
     */
    private function showMessage(string $message, string $foreground = '', string $background = '')
    {
        if ($background) {
            $message = "\n\n$message\n";
        }
        echo "\n" . $this->applyStyle($message, $foreground, $background) . "\n\n";
    }

    /**
     * If call undefined method and method name has prefix is text and exist Formater color, show this message with that color.
     *
     * @param  string $name
     * @param  array  $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        $color = ltrim($name, 'text');
        if (preg_match("/^text/", $name) && (isset(Formater::COLORS[strtolower($color)]) || isset(Formater::BRIGHT_COLORS[$color = Str::kebab($color)])) ) {
            return $this->showMessage($arguments[0], strtolower($color));
        }

        throw new BadMethodCallException("Method $name do not exist or not allow call.");
    }
}

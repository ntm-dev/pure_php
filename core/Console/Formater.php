<?php

namespace Core\Console;

use InvalidArgumentException;

final class Formater
{
    public const COLORS = [
        'black' => 0,
        'red' => 1,
        'green' => 2,
        'yellow' => 3,
        'blue' => 4,
        'magenta' => 5,
        'cyan' => 6,
        'white' => 7,
        'default' => 9,
    ];

    public const BRIGHT_COLORS = [
        'gray' => 0,
        'bright-red' => 1,
        'bright-green' => 2,
        'bright-yellow' => 3,
        'bright-blue' => 4,
        'bright-magenta' => 5,
        'bright-cyan' => 6,
        'bright-white' => 7,
    ];

    private const AVAILABLE_OPTIONS = [
        'bold' => ['set' => 1, 'unset' => 22],
        'underscore' => ['set' => 4, 'unset' => 24],
        'blink' => ['set' => 5, 'unset' => 25],
        'reverse' => ['set' => 7, 'unset' => 27],
        'conceal' => ['set' => 8, 'unset' => 28],
    ];

    public const ESCAPE = "\e[0m";

    private string $foreground;
    private string $background;
    private array  $options = [];

    public function __construct(string $foreground = '', string $background = '', array $options = [])
    {
        $this->foreground = $this->parseColor($foreground);
        $this->background = $this->parseColor($background, true);

        foreach ($options as $option) {
            if (!isset(self::AVAILABLE_OPTIONS[$option])) {
                throw new InvalidArgumentException(sprintf('Invalid option specified: "%s". Expected one of (%s).', $option, implode(', ', array_keys(self::AVAILABLE_OPTIONS))));
            }

            $this->options[$option] = self::AVAILABLE_OPTIONS[$option];
        }
    }

    private function parseColor(string $color, bool $background = false): string
    {
        if ('' === $color) {
            return '';
        }

        if (isset(self::COLORS[$color])) {
            return ($background ? '4' : '3').self::COLORS[$color];
        }

        if (isset(self::BRIGHT_COLORS[$color])) {
            return ($background ? '10' : '9').self::BRIGHT_COLORS[$color];
        }

        throw new InvalidArgumentException(sprintf('Invalid "%s" color; expected one of (%s).', $color, implode(', ', array_merge(array_keys(self::COLORS), array_keys(self::BRIGHT_COLORS)))));
    }

    public function apply($text)
    {
        $setCodes = [];
        if ('' !== $this->foreground) {
            $setCodes[] = $this->foreground;
        }
        if ('' !== $this->background) {
            $setCodes[] = $this->background;
        }
        foreach ($this->options as $option) {
            $setCodes[] = $option['set'];
        }

        $code = implode(';', $setCodes);

        return "\e[{$code}m{$text}" . self::ESCAPE;
    }

}

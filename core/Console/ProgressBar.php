<?php

namespace Core\Console;

use Core\Console\Formater;

/**
 * ProgressBar class.
 *
 * @author Nguyen The Manh <nguyenthemanh26011996@gmail.com>
 */
final class ProgressBar
{
    /** Fill character */
    const FILL_CHAR = "▓";

    /** Not fill character */
    const NOT_FILL_CHAR = "░";

    /** Max length for progress bar */
    const BAR_LENGTH = 50;

    /** @var int Max step of progress bar */
    private $maxProcess = 0;

    /** @var int Current step of progress bar */
    private $currenProcess = 0;

    /** @var bool End status of progress bar */
    private $endProgress = false;

    /** @var string Process bar string */
    private  $progressBar = "";

    /** Colors list */
    private $colors = [];

    /** Init a progress bar */

    public function __construct($max)
    {
        $this->maxProcess = $max;
        $this->initColorCode();
    }

    private function initColorCode()
    {
        for ($i = 196; $i <= 231; $i++) {
            $this->colors[] = $i;
        }
        for ($i = 123; $i >= 119; $i--) {
            for ($j = 0; $j < 3; $j++) {
                $this->colors[] = $i - 36 * $j;
            }
        }
    }

    private function getColorCode($percent)
    {
        $code = floor($percent / 2);
        if (!isset($this->colors[$code])) {
            $code = $this->colors[count($this->colors) - 1];
        } else {
            $code = $this->colors[$code];
        }
        return "\e[38;5;{$code}m";
    }

    public function start()
    {
        echo "\n\033[?25l"; // show break line and hide cursor
        $this->showProcess(0);
    }

    private function createProgress($percent)
    {
        $bar = '';
        if ($percent >= 100) {
            $percent = 100;
        }
        $fillPoint = floor($percent / (floor(100 / static::BAR_LENGTH)));
        for ($i = 0; $i < $fillPoint; $i++) {
            $bar .= static::FILL_CHAR;
        }
        $notFill = static::BAR_LENGTH - $fillPoint;
        for ($i = 0; $i < $notFill - 1; $i++) {
            $bar .= static::NOT_FILL_CHAR;
        }

        return $bar;
    }

    private function getBackward($percent)
    {
        $backward = (strlen($this->progressBar) + 2);
        if ($percent < 10) {
            $backward++;
        }

        return  "\033[{$backward}D ";
    }

    private function showProcess($percent)
    {
        $backward = $this->getBackward($percent);
        $bar = $this->createProgress($percent);

        $colorCode = $this->getColorCode($percent);

        $total = "{$this->currenProcess}/" . ($this->currenProcess > $this->maxProcess ? $this->currenProcess : $this->maxProcess);
        echo $this->progressBar = "{$backward}{$colorCode} {$total} "
            . Formater::ESCAPE
            . " [{$colorCode}{$bar}"
            . Formater::ESCAPE
            . "] {$colorCode}{$percent} %"
            . Formater::ESCAPE;
    }

    public function advance($step = 1)
    {
        $this->currenProcess += $step;
        $percent = floor($this->currenProcess * 100 / $this->maxProcess);

        $this->showProcess($percent);

        if ($percent >= 100 && !$this->endProgress) {
            $this->endProgress = true;
        }
    }

    public function finish()
    {
        $this->endProgress = true;
        echo "\n";
        fprintf(STDOUT, "\033[?25h"); //show cursor
    }

    public function __destruct()
    {
        $this->finish();
    }
}

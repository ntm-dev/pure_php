<?php

namespace Core\Console;

use Core\Console\Formater;

class ProgressBar
{
    /** Fill character */
    private const FILL_CHAR = "▓";

    /** Not fill character */
    private const NOT_FILL_CHAR = "░";

    /** Max length for progress bar */
    private const BAR_LENGTH = 50;

    /** Color code for success bar */
    private const SUCCESSS_COLOR_CODE = "\e[38;5;34m";

    /** Color code for inprogress bar */
    private const INPROGRESS_COLOR_CODE = "\e[38;5;202m";

    /** Max step of progress bar */
    private int $maxProcess = 0;

    /** Current step of progress bar */
    private int $currenProcess = 0;

    /** End status of progress bar */
    private bool $endProgress = false;

    /** Process bar string */
    private string $progressBar = "";

    /** Init a progress bar */
    public function __construct(int $max)
    {
        $this->maxProcess = $max;
    }

    /**
     * Start and show progress bar.
     *
     * @return void
     */
    public function start()
    {
        echo "\n";
        echo $this->progressBar = static::INPROGRESS_COLOR_CODE
                            . " 0/{$this->maxProcess} "
                            . Formater::ESCAPE
                            . " ["
                            . static::INPROGRESS_COLOR_CODE
                            . $this->createProgress(0)
                            . Formater::ESCAPE
                            . "]"
                            . static::INPROGRESS_COLOR_CODE . "0 %"
                            . Formater::ESCAPE;
    }

    /**
     * Create progress.
     *
     * @param  int $percent
     * @return string
     */
    private function createProgress(int $percent)
    {
        $bar = '';
        if ($percent >= 100) {
            $percent = 100;
        }
        $fillPoint = floor($percent / 2);
        for ($i = 0; $i < $fillPoint; $i++) {
            $bar .= static::FILL_CHAR;
        }
        $notFill = static::BAR_LENGTH - $fillPoint;
        for ($i = 0; $i < $notFill - 1; $i++) {
            $bar .= static::NOT_FILL_CHAR;
        }

        return $bar;
    }

    /**
     * Advance exist progress bar.
     *
     * @param  int $step
     * @return void
     */
    public function advance(int $step = 1)
    {
        $this->currenProcess += $step;
        $percent = floor($this->currenProcess * 100 / $this->maxProcess);
        $bar = $this->createProgress($percent);

        $backward = (strlen($this->progressBar) + 2);
        if ($percent < 10) {
            $backward++;
        }
        $backward = "\033[{$backward}D ";

        $colorCode = $percent >= 100 ? static::SUCCESSS_COLOR_CODE : static::INPROGRESS_COLOR_CODE;

        $total = "{$this->currenProcess}/" . ($this->currenProcess > $this->maxProcess ? $this->currenProcess : $this->maxProcess);
        echo $this->progressBar = "{$backward}{$colorCode} {$total} "
                            . Formater::ESCAPE
                            . " [{$colorCode}{$bar}"
                            . Formater::ESCAPE
                            . "] {$colorCode}{$percent} %"
                            . Formater::ESCAPE;

        if ($percent >= 100 && !$this->endProgress) {
            $this->endProgress = true;
        }
    }

    /**
     * Finish progress bar and show break line.
     *
     * @return void
     */
    public function finish()
    {
        $this->endProgress = true;
        echo "\n";
    }
}

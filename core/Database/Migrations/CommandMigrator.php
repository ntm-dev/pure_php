<?php

namespace Core\Database\Migrations;

use Exception;
use Core\Console\Command;
use Core\File\LocalAdapter;
use Core\Support\Helper\Arr;
use Core\Support\Helper\Str;
use Core\Console\ColorFormat;
use Core\Contract\CommandExecutor;
use Core\Database\Connectors\Connector;

/**
 * Support Command Migrator.
 *
 * @author Nguyen The Manh <nguyenthemanh26011996@gmail.com>
 */
class CommandMigrator implements CommandExecutor
{
    /** @var \Core\Console\Command */
    protected $command;

    public function __construct()
    {
        $this->command = container(Command::class, true);
    }

    /**
     * Run migrate
     *
     * @param  string  $options
     * @return string
     */
    public function migrate($options = '', $value = "")
    {
        $files = $this->filterFileByOption(...func_get_args());

        $migrated = 0;
        foreach ($files as $file) {
            try {
                $this->migrateFile($file);
                $this->command->textGreen(sprintf("Migrated %s", $this->underscoreText($file)));
            } catch (\Exception $e) {
                dump($e);
            }
            ++$migrated;
        }
        if (!$migrated) {
            $this->command->info("Nothing to migrate");
        }
    }

    public function rollback($options = '', $value = "")
    {
        $files = $this->filterFileByOption(...func_get_args());

        $recovered = 0;
        foreach ($files as $file) {
            $underscoreText = $this->underscoreText($file);
            true === $this->rollbackFile($file) ? $this->command->textGreen("Rollback {$underscoreText}") : $this->command->textRed("Rollback fail {$underscoreText}");
            ++$recovered;
        }
        if (!$recovered) {
            $this->command->info("Nothing to rollback");
        }
    }

    private function filterFileByOption($options = '', $value = "")
    {
        if (Str::contains($options, "--file=")) {
            $filename = Str::after($options, "--file=");
            $filename = Str::before($filename, " ") ?: $filename;
            if (!file_exists($filename)) {
                $filename = base_path($filename);
            }
            return $filename;
        }
        if (Str::contains($options, "--after")) {
            if (empty($value) || false === strtotime($value)) {
                return $this->command->textRed("Option --after must be datetime format.");
            }
            $time = date("YmdHis", strtotime($value));
        }
        if (Str::contains($options, "--contain")) {
            if (Str::isEmpty($value)) {
                return $this->command->textRed("Option --contain cannot be empty.");
            }
            $contain = Str::trim($value);
        }

        $files = $this->filer()->allFiles(MigrationCreator::path(), true);

        foreach ($files as $key => $file) {
            if (isset($time)) {
                $fileTime = Str::before(Str::afterLast($file, DIRECTORY_SEPARATOR), "_");
                if ($time > $fileTime) {
                    unset($files[$key]);
                    continue;
                }
            } elseif (isset($contain)) {
                if (!Str::contains($file, $contain)) {
                    unset($files[$key]);
                    continue;
                }
            }
        }

        return $files;
    }

    /**
     * Create underscore text
     *
     * @param  string  $text
     * @return string
     */
    private function underscoreText($text)
    {
        return rtrim(rtrim($this->command->underscore($text, false), "\n"), ColorFormat::ESCAPE);
    }

    /**
     * @return \Core\File\LocalAdapter
     */
    private function filer()
    {
        return container(LocalAdapter::class, true);
    }

    /**
     * Run migrate for a file
     *
     * @param  string  $filename
     * @return bool
     */
    public function migrateFile($filename)
    {
        $this->registerShutdownFunction();
        try {
            if (!$migration = require $filename) {
                $this->command->textRed("{$filename} does not exists.");
                die;
            }

            if ($sql = $migration->up()) {
                $this->dbConnection($migration->getConnection())->connect()->exec($sql);
            }
        } catch (Exception $e) {
            throw $e;
        }

        return true;
    }

    /**
     * Run rollback for a file
     *
     * @param  string  $filename
     * @return bool
     */
    public function rollbackFile($filename)
    {
        $this->registerShutdownFunction();
        try {
            if (!@include $filename) {
                $this->command->textGreen("{$filename} does not exists.");
                die;
            }
            $className = Arr::last(get_declared_classes());
            $class = new $className;

            if ($sql = $class->down()) {
                $this->dbConnection($class->getConnection())->connect()->exec($sql);
            }
        } catch (Exception $e) {
            dump($e);
        }

        return true;
    }

    /**
     * @return \Core\Database\Connectors\Connector;
     */
    private function dbConnection($connectionName = '')
    {
        /** @var \Core\Database\Connectors\Connector; */
        $instance = container(Connector::class, true);
        $instance->setConnectionName($connectionName);

        return $instance;
    }

    private function registerShutdownFunction()
    {
        register_shutdown_function(function () {
            if ($error = get_last_fatal_error()) {
                dump($error);
            }
        });
    }
}

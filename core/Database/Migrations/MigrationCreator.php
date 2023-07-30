<?php

namespace Core\Database\Migrations;

use Core\Support\Helper\Str;
use UnexpectedValueException;
use Core\File\LocalAdapter;
use Core\Contract\CommandExecutor;
use Core\Database\Migrations\Migration;

/**
 * Support Migration Creator.
 *
 * @author Nguyen The Manh <nguyenthemanh26011996@gmail.com>
 */
class MigrationCreator implements CommandExecutor
{
    /** @var string */
    private static ?string $path = null;

    /**
     * Get/set migration path.
     *
     * @param  string  $path
     * @return string
     */
    public static function path($path = '')
    {
        if ($path) {
            return base_path(self::$path = $path);
        } elseif (!self::$path) {
            return self::$path = base_path(config('database.migrations'));
        }

        return self::$path;
    }

    /**
     * Create migration file
     *
     * @param  string  $name
     * @return string|bool
     */
    public function create(&$name, $options = '')
    {
        if (Str::isEmpty($name)) {
            throw new UnexpectedValueException(sprintf("Argument #1 passed to %s can not be empty", __METHOD__));
        }

        $filePath = $this->createFilePath($name, $options);

        $template = $this->createStub();

        $result = $this->filer()->put($filePath, $template);

        return $result ? $filePath : false;
    }

    private function createFilePath(&$name, $options = '')
    {
        if (Str::contains($options, "--path=")) {
            $path = Str::after($options, "--path=");
            $path = self::path(trim(Str::before($path, " ") ?: $path, DIRECTORY_SEPARATOR));
        } else {
            $path = self::path() . DIRECTORY_SEPARATOR . Str::beforeLast($name, DIRECTORY_SEPARATOR);
        }

        $path = rtrim($path, DIRECTORY_SEPARATOR);
        $name = Str::snake(Str::afterLast($name, DIRECTORY_SEPARATOR));

        return sprintf("%s%s%s_%s.php", $path, DIRECTORY_SEPARATOR, date('YmdHis'), $name);
    }

    /**
     * @return \Core\File\LocalAdapter
     */
    protected function filer()
    {
        return container(LocalAdapter::class, true);
    }

    /**
     * Create migration stub
     *
     * @return string
     */
    public function createStub()
    {
        $migrationClass = Migration::class;
        return <<<PHP
<?php

use {$migrationClass};

return new class extends Migration
{
    /**
     * The name of the database connection to use.
     *
     * @var string
     */
    protected \$connection;

    /**
     * Run the migrations.
     *
     * @return string  Return sql string
     */
    public function up()
    {
        /**
        return <<<SQL
-- Write SQL here
SQL;
        */
    }

    /**
     * Reverse the migrations.
     *
     * @return  string  Return sql string
     */
    public function down()
    {
        /**
        return <<<SQL
-- Write SQL here
SQL;
        */
    }
};

PHP;
    }
}

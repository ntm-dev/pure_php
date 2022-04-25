<?php

namespace Core\Database;

use PDO;
use Core\Pattern\Singleton;
use Core\Database\PDO\Connector;
class Builder
{
    use Connector, Singleton;

    /**
     * Selected columns.
     *
     * @var array
     */
    public static $selects = ['*'];

    /**
     * The where constraints for the query.
     *
     * @var array
     */
    public $wheres = [];

    /**
     * The orderings for the query.
     *
     * @var array
     */
    public static $orders;

    /**
     * The maximum number of records to return.
     *
     * @var int
     */
    public static $limit;

    /**
     * The number of records to skip.
     *
     * @var int
     */
    public static $ofset;

    /**
     * Get database connection.
     *
     * @return PDO|false
     */
    protected function getConnection()
    {
        return static::$connection ?: $this->connect();
    }

    public function select(array $cols = ['*'])
    {
        self::$selects = $cols;
        return $this->getInstance();
    }

    /**
     * Add a basic where clause to the query.
     *
     * @param  string|array  $column
     * @param  mixed  $operator
     * @param  mixed  $value
     * @return $this
     */
    public function where($column, $operator = null, $value = null)
    {
        if (is_array($column)) {
            return $this->addArrayOfWheres($column);
        }

        return $this->addArrayOfWheres(...func_get_args());
    }

    /**
     * Add an array of where clauses to the query.
     *
     * @param  array  $column
     * @param  string  $method
     * @return $this
     */
    protected function addArrayOfWheres($column, $method = 'where')
    {
        if (is_string(end($column))) {
            $column[count($column) - 1] = "'" . end($column) . "'";
        }
        $this->wheres[] = implode(' ', $column);

        return $this;
    }

    public function get()
    {
        try {
            $stmt = $this->getConnection()->query($this->buildSelectQuery());
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $th) {
            dd($th->getMessage());
        }
    }

    private function buildSelectQuery()
    {
        $selectStr = implode(', ', self::$selects);
        $sql = "SELECT $selectStr FROM " . $this->table();
        if (!empty($this->wheres)) {
            $sql .= " WHERE " . implode (' AND ', $this->wheres);
        }
        return $sql;
    }

    private function table()
    {
        if (!$this->table) {
            throw new \RuntimeException("Please define table name");
        }
        return $this->table;
    }
}

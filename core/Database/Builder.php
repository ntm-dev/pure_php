<?php

namespace Core\Database;

use Core\Database\Model;
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
    public $selects = ['*'];

    /**
     * The where constraints for the query.
     *
     * @var array
     */
    public $wheres = [];

    /**
     * The current query value bindings.
     *
     * @var array
     */
    public $bindings = [
        'where' => [],
    ];

    /**
     * The orderings for the query.
     *
     * @var array
     */
    public $orders;

    /**
     * The maximum number of records to return.
     *
     * @var int
     */
    public $limit;

    /**
     * The number of records to skip.
     *
     * @var int
     */
    public $offset;

    public $model;

    public function __construct(Model $model = null)
    {
        if ($model) {
            $this->model = $model;
            $this->setTable($this->getModelTableName());
        }
    }

    /**
     * Get database connection.
     *
     * @return \PDO|false
     */
    protected function getConnection()
    {
        return static::$connection ?: $this->connect();
    }

    public function select(array $cols = ['*'])
    {
        $this->selects = $cols;

        return $this;
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
            return $this->addWhereQuery(...$column);
        }

        return $this->addWhereQuery(...func_get_args());
    }

    /**
     * Add a binding to the query.
     *
     * @param  mixed  $value
     * @param  string  $type
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function addBinding($value, $type = 'where')
    {
        if (! array_key_exists($type, $this->bindings)) {
            throw new \InvalidArgumentException("Invalid binding type: {$type}.");
        }

        if (is_array($value)) {
            $this->bindings[$type] = array_values(array_merge($this->bindings[$type], $value));
        } else {
            $this->bindings[$type][] = $value;
        }

        return $this;
    }

    /**
     * Add single condition to query
     *
     * @param  string|array  $column
     * @param  mixed  $operator
     * @param  mixed  $value
     * @return $this
     */
    protected function addWhereQuery($column, $operator = null, $value = null)
    {
        $this->wheres[] = "$column " . ($operator ?: "=") . " ?";
        $this->addBinding($value);

        return $this;
    }

    public function get()
    {
        return $this->fetchAll();
    }

    private function query(\PDOStatement $stmt = null)
    {
        $sth = $stmt ?: $this->getConnection()->prepare($this->buildSelectQuery());

        $sth->execute($this->bindings['where']);

        return $sth;
    }

    private function fetchAll(\PDOStatement $stmt = null)
    {
        try {
            $result = $this->query($stmt)->fetchAll(\PDO::FETCH_ASSOC);
            if (empty($result)) {
                return null;
            }

            foreach ($result as &$value) {
                $value = new (get_class($this->model))($value);
            }

            $this->model->setOriginal($result);
            $this->model->setAttributes($result);

            return $this->model;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    private function fetch(\PDOStatement $stmt = null)
    {
        try {
            $result = $this->query($stmt)->fetch(\PDO::FETCH_ASSOC);

            if (empty($result)) {
                return null;
            }

            $this->model->setOriginal($result);
            $this->model->setAttributes($result);

            return $this->model;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function first()
    {
        $this->limit = 1;

        return $this->fetch();
    }

    private function buildSelectQuery()
    {
        $selectStr = implode(', ', $this->selects);
        $sql = "SELECT $selectStr FROM " . $this->getTable();

        if (!empty($this->wheres)) {
            $sql .= $this->buildWhereConditions();
        }

        if ($this->limit) {
            $sql .= " LIMIT " . (is_null($this->offset) ? "" : "$this->offset,") . "{$this->limit}";
        }

        return $sql;
    }

    private function buildWhereConditions()
    {
        $conditionStr = "";
        foreach ($this->wheres as $value) {
            $conditionStr .= $value;
        }

        return " WHERE $conditionStr";
    }

    private function getModelTableName()
    {
        if (empty($this->model->table)) {
            throw new \RuntimeException("Please define table name");
        }

        return $this->model->table;
    }

    public function setTable($table)
    {
        $this->table = $table;
    }

    public function getTable()
    {
        return $this->table;
    }

    public function table(string $name)
    {
        $this->setTable($name);

        return $this;
    }
}

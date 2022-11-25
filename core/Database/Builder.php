<?php

namespace Core\Database;

use Core\Database\Model;
use Core\Pattern\Singleton;
use Core\Database\Connectors\Connector;

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
    public $bindings = [];

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

    public function select($columns = ['*'])
    {
        $this->selects = is_array($columns) ? $columns : func_get_args();

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
    public function addBinding($value)
    {
        $this->bindings[] = $value;

        return $this;
    }

    /**
     * Multiple binding.
     *
     * @param  array $values
     * @param  string  $type
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function addBindings(array $values)
    {
        foreach ($values as $value) {
            $this->addBinding($value);
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

    private function query($sql, \PDO $con = null)
    {
        $statement = ($con ?: $this->getConnection())->prepare($sql);

        $this->bindValues($statement, $this->bindings);

        $statement->execute();

        return $statement;
    }

    /**
     * Bind values to their parameters in the given statement.
     *
     * @param  \PDOStatement  $statement
     * @param  array  $bindings
     * @return void
     */
    public function bindValues($statement, $bindings)
    {
        foreach ($bindings as $key => $value) {
            $statement->bindValue(
                is_string($key) ? $key : $key + 1,
                $value,
                is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR
            );
        }
    }

    private function fetchAll(\PDOStatement $stmt = null)
    {
        try {
            $result = $this->query($this->buildSelectQuery(), $stmt)->fetchAll(\PDO::FETCH_ASSOC);
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
            $result = $this->query($this->buildSelectQuery(), $stmt)->fetch(\PDO::FETCH_ASSOC);

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

    public function take($num)
    {
        $this->limit = $num;

        return $this;
    }

    public function skip($num)
    {
        $this->offset = $num;

        return $this;
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

    /**
     * Count record
     *
     * @return int|false
     */
    public function count()
    {
        $this->select("SQL_CALC_FOUND_ROWS *");
        if (false !== $stm = $this->query($this->buildSelectQuery())) {
            return $stm->rowCount();
        }

        return false;
    }

    /**
     * Check exist
     *
     * @return  bool
     */
    public function exist()
    {
        return !!$this->count();
    }

    public function create(array $values)
    {
        $this->model->setAttributes($values);
        $this->fireModelEvent('creating');

        $result = $this->model->save();
        if ($result) {
            $this->fireModelEvent('created');
        }

        return $result;
    }

    /**
     * Insert new records into the database.
     *
     * @param  array  $values
     * @return bool
     */
    public function insert(array $values)
    {
        if (empty($values)) {
            return true;
        }

        if (! is_array(reset($values))) {
            $values = [$values];
        }

        try {
            $this->query($this->compileInsert($values));
            return true;
        } catch (\Throwable $th) {
            throw $th;
            return false;
        }
    }

    public function insertGetId(array $values)
    {
        if ($this->insert($values)) {
            return $this->getConnection()->lastInsertId();
        }

        return false;
    }

    /**
     * Compile an insert statement into SQL.
     *
     * @param  array  $values
     * @return string
     */
    public function compileInsert(array $values)
    {
        $table = $this->getTable();

        if (! is_array(reset($values))) {
            $values = [$values];
        }

        $columns = implode(", ", array_keys(reset($values)));

        $parameters = implode(", ", array_map(function($vals) {
            $this->addBindings($vals, "insert");
            return "(" . implode(", ", array_map(function() { return "?"; }, $vals)) . ")";
        }, $values));

        return "insert into $table ($columns) values $parameters";
    }

    public function update(array $attributes = [])
    {
        if (!$this->exist()) {
            return false;
        }

        // return $this->fill()
    }

    public function fireModelEvent(string $event)
    {
        if (!method_exists($this->model, $event)) {
            return;
        }

        return $this->model->{$event}($this->model);
    }
}

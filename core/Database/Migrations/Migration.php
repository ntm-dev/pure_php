<?php

namespace Core\Database\Migrations;

/**
 * Support Database Migration Abstraction.
 *
 * @author Nguyen The Manh <nguyenthemanh26011996@gmail.com>
 */
abstract class Migration
{
    /**
     * The name of the database connection to use.
     *
     * @var string
     */
    protected $connection;

    /**
     * Get the migration connection name.
     *
     * @return string
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Run the migrations.
     *
     * @return  string  Return sql string
     */
    abstract public function up();

    /**
     * Reverse the migrations.
     *
     * @return  string  Return sql string
     */
    abstract public function down();
}

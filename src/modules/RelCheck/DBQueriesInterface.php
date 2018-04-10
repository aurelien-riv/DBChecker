<?php

namespace DBChecker\modules\RelCheck;

interface DBQueriesInterface extends \DBChecker\DBQueries\DBQueriesInterface
{
    /**
     * @return bool|\PDOStatement
     */
    public function getTableNames();

    /**
     * @return bool|\PDOStatement
     */
    public function getFks();

    /**
     * @param string $table
     * @return bool|\PDOStatement
     */
    public function getColumnNamesInTable($table);

    /**
     * @param string          $table
     * @param string|string[] $columns
     * @return bool|\PDOStatement
     */
    public function getDistinctValuesWithoutNulls($table, $columns);

    /**
     * @param string $table
     * @param string $column
     * @param $value
     * @return bool|\PDOStatement
     */
    public function getValue($table, $column, $value);
}
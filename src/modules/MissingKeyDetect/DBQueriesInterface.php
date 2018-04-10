<?php

namespace DBChecker\modules\MissingKeyDetect;

interface DBQueriesInterface extends \DBChecker\DBQueries\DBQueriesInterface
{
    /**
     * @param string $table
     * @return bool|\PDOStatement
     */
    public function getPKs($table);

    /**
     * @return bool|\PDOStatement
     * Get all the {TABLE_NAME, COLUMN_NAME} from the database
     */
    public function getColumnNamesWithTableName();

    /**
     * @param string $table
     * @param string $column
     * @return bool|\PDOStatement
     * Get the table name and the column name on the other side of a foreign key relation
     */
    public function getDistantTableAndColumnFromTableAndColumnFK($table, $column);
}
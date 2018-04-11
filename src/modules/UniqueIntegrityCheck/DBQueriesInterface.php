<?php

namespace DBChecker\modules\UniqueIntegrityCheck;

interface DBQueriesInterface extends \DBChecker\DBQueries\DBQueriesInterface
{
    /**
     * @return bool|\PDOStatement
     */
    public function getTableNames();

    /**
     * @param string $table
     * @return bool|\PDOStatement
     */
    public function getUniqueIndexes($table);

    /**
     * @param string $table The table name
     * @param string $columns A coma separated list of columns
     * If $columns references a single column (= the string contains no coma), NULL values won't be considered as
     * duplicates, otherwise a null column will be treated as a duplicate contrary to the SQL norm as it may be
     * not wanted.
     */
    public function getDuplicateForColumnsWithCount($table, $columns);
}
<?php

namespace DBChecker\DBQueries;

abstract class AbstractDbQueries
{
    protected $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public abstract function getTableNames();

    public abstract function getFks();

    public abstract function getUniqueIndexes($table);

    /**
     * @param string $table The table name
     * @param string $columns A coma separated list of columns
     * If $columns references a single column (= the string contains no coma), NULL values won't be considered as
     * duplicates, otherwise a null column will be treated as a duplicate contrary to the SQL norm as it may be
     * not wanted.
     */
    public abstract function getDuplicateForColumnsWithCount($table, $columns);

    public abstract function getDistinctValuesWithoutNulls($table, $column);

    public abstract function getValue($table, $column, $value);

    /**
     * @param  string $table The table name
     * @return string A coma separated list of all the columns of the table
     */
    public abstract function getConcatenatedColumnNames($table);

    /**
     * @param  string $table The table name
     * @return string The Sha1 sum of the concatenation of the data from all the columns and then all the rows.
     * The order of the concatenation matters as a change in the concatenation would change the checksum.
     */
    public abstract function getTableDataSha1sum($table);

    public abstract function getTableSchemaSha1sum($table);
}
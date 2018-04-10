<?php

namespace DBChecker\DBQueries;

abstract class AbstractDbQueries implements
    \DBChecker\modules\MissingCompressionDetect\DBQueriesInterface
{
    /**
     * Regex that matches a valid column name
     */
    const IDENTIFIER = '[a-zA-Z_][a-zA-Z0-9_]*';

    protected $pdo;
    protected $name;

    public function __construct(\PDO $pdo, $name)
    {
        $this->pdo = $pdo;
        $this->name = $name;
    }

    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @return bool|\PDOStatement
     */
    public abstract function getTableNames();

    /**
     * @return bool|\PDOStatement
     * Get all the {TABLE_NAME, COLUMN_NAME} from the database
     */
    public abstract function getColumnNamesWithTableName();

    /**
     * @param string $table
     * @return bool|\PDOStatement
     */
    public abstract function getColumnNamesInTable($table);

    /**
     * @param string $table
     * @return bool|\PDOStatement
     */
    public abstract function getPKs($table);

    /**
     * @return bool|\PDOStatement
     */
    public abstract function getFks();

    /**
     * @param string $table
     * @return bool|\PDOStatement
     */
    public abstract function getUniqueIndexes($table);

    /**
     * @param string $table
     * @param string $column
     * @return bool|\PDOStatement
     * Get the table name and the column name on the other side of a foreign key relation
     */
    public abstract function getDistantTableAndColumnFromTableAndColumnFK($table, $column);

    /**
     * @param string $table The table name
     * @param string $columns A coma separated list of columns
     * If $columns references a single column (= the string contains no coma), NULL values won't be considered as
     * duplicates, otherwise a null column will be treated as a duplicate contrary to the SQL norm as it may be
     * not wanted.
     */
    public abstract function getDuplicateForColumnsWithCount($table, $columns);

    /**
     * @param string          $table
     * @param string|string[] $columns
     * @return bool|\PDOStatement
     */
    public abstract function getDistinctValuesWithoutNulls($table, $columns);

    /**
     * @param string   $table
     * @param string[] $columns
     * @param string[] $innerJoinColumns
     * @return bool|\PDOStatement
     */
    public abstract function getDistinctValuesWithJoinColumnsWithoutNulls($table, $columns, $innerJoinColumns);

    /**
     * @param string $table
     * @param string $column
     * @param $value
     * @return bool|\PDOStatement
     */
    public abstract function getValue($table, $column, $value);

    /**
     * @param  string $table The table name
     * @return string A coma separated list of all the columns of the table
     */
    public abstract function getConcatenatedColumnNames($table);

    #region Checksums
    /**
     * @param  string $table The table name
     * @return string The Sha1 sum of the concatenation of the data from all the columns and then all the rows.
     * The order of the concatenation matters as a change in the concatenation would change the checksum.
     */
    public abstract function getTableDataSha1sum($table);

    /**
     * @param $table
     * @return string
     */
    public abstract function getTableSchemaSha1sum($table) : string;
    #endregion

    public function supportsTablespaceCompression() : bool
    {
        return false;
    }

    public function isTableCompressed(string $table) : bool
    {
        return false;
    }
}
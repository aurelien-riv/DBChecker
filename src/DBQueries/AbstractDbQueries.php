<?php

namespace DBChecker\DBQueries;

use BadMethodCallException;

abstract class AbstractDbQueries implements
    \DBChecker\modules\MissingCompressionDetect\DBQueriesInterface,
    \DBChecker\modules\DataIntegrityCheck\DBQueriesInterface,
    \DBChecker\modules\SchemaIntegrityCheck\DBQueriesInterface,
    \DBChecker\modules\FileCheck\DBQueriesInterface,
    \DBChecker\modules\MissingKeyDetect\DBQueriesInterface,
    \DBChecker\modules\RelCheck\DBQueriesInterface,
    \DBChecker\modules\FragmentationCheck\DBQueriesInterface,
    \DBChecker\modules\UniqueIntegrityCheck\DBQueriesInterface
{
    /**
     * Regex that matches a valid column name
     */
    const IDENTIFIER = '[a-zA-Z_][a-zA-Z0-9_]*';

    protected $pdo;
    protected $name;

    const NOT_IMPLEMENTED_ERROR_MSG = "This method is not available for this database";
    
    public function __construct(\PDO $pdo, $name)
    {
        $this->pdo = $pdo;
        $this->name = $name;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getValue($table, $column, $value)
    {
        $stmt = $this->pdo->prepare("SELECT DISTINCT $column FROM $table WHERE $column = :value LIMIT 1;");
        $stmt->bindParam(':value', $value, \PDO::PARAM_STR);
        $stmt->execute();
        return $stmt;
    }

    public function getDuplicateForColumnsWithCount($table, $columns)
    {
        $query = "
            SELECT $columns, COUNT(*) as __count__
            FROM $table
            GROUP BY $columns
            HAVING COUNT(*) > 1
        ";
        // If there is one column, ignore null values
        if (! strpos($columns, ','))
        {
            $query .= " AND $columns IS NOT NULL";
        }
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getDistinctValuesWithoutNulls($table, $columns)
    {
        $selectColumns = $whereColumns = $columns;
        if (is_array($columns))
        {
            $selectColumns = implode(',', $columns);
            $whereColumns = implode(' IS NOT NULL AND ', $columns);
        }
        $query = "SELECT DISTINCT $selectColumns FROM $table WHERE $whereColumns IS NOT NULL;";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    /**
     * @param string   $table
     * @param string[] $columns
     * @param string[] $innerJoinColumns
     * @return bool|\PDOStatement
     * Warning, composed key are not supported yet
     */
    public function getDistinctValuesWithJoinColumnsWithoutNulls($table, $columns, $innerJoinColumns) : \PDOStatement
    {
        $columns          = array_unique($columns);
        $innerJoinColumns = array_unique($innerJoinColumns);

        $joins = '';
        foreach ($innerJoinColumns as $innerJoinColumn)
        {
            $relation = $this->getDistantTableAndColumnFromTableAndColumnFK($table, $innerJoinColumn)->fetch(\PDO::FETCH_OBJ);

            $joins .= "INNER JOIN {$relation->REFERENCED_TABLE_NAME} AS $innerJoinColumn
                ON $innerJoinColumn.{$relation->REFERENCED_COLUMN_NAME} = $table.$innerJoinColumn ";
        }

        $selectColumns = '';
        foreach ($columns as $column)
        {
            $selectColumns .= "$column as `$column`,";
        }
        $stmt = $this->pdo->prepare("SELECT DISTINCT " . rtrim($selectColumns, ',')
                                    . " FROM $table $joins"
                                    . " WHERE " . implode(' IS NOT NULL AND ', $columns) . " IS NOT NULL;");
        $stmt->execute();
        return $stmt;
    }

    public function supportsTablespaceCompression() : bool
    {
        return false;
    }

    public function isTableCompressed(string $table) : bool
    {
        return false;
    }

    public function getUniqueIndexes($table)
    {
        throw new BadMethodCallException(static::NOT_IMPLEMENTED_ERROR_MSG);
    }

    public function getTableLargerThanMb(int $minSize_MB) : \PDOStatement
    {
        throw new BadMethodCallException(static::NOT_IMPLEMENTED_ERROR_MSG);
    }

    public function getRandomValuesConcatenated(string $table, int $limit) : \PDOStatement
    {
        throw new BadMethodCallException(static::NOT_IMPLEMENTED_ERROR_MSG);
    }

    public function getTableNames()
    {
        throw new BadMethodCallException(static::NOT_IMPLEMENTED_ERROR_MSG);
    }

    public function getTableDataSha1sum($table)
    {
        throw new BadMethodCallException(static::NOT_IMPLEMENTED_ERROR_MSG);
    }

    public function getTableSchemaSha1sum($table) : string
    {
        throw new BadMethodCallException(static::NOT_IMPLEMENTED_ERROR_MSG);
    }

    public function getPKs($table)
    {
        throw new BadMethodCallException(static::NOT_IMPLEMENTED_ERROR_MSG);
    }

    public function getColumnNamesWithTableName()
    {
        throw new BadMethodCallException(static::NOT_IMPLEMENTED_ERROR_MSG);
    }

    public function getDistantTableAndColumnFromTableAndColumnFK($table, $column)
    {
        throw new BadMethodCallException(static::NOT_IMPLEMENTED_ERROR_MSG);
    }

    public function getFks()
    {
        throw new BadMethodCallException(static::NOT_IMPLEMENTED_ERROR_MSG);
    }

    public function getColumnNamesInTable($table)
    {
        throw new BadMethodCallException(static::NOT_IMPLEMENTED_ERROR_MSG);
    }

    public function getFragmentedTables() : \PDOStatement
    {
        throw new BadMethodCallException(static::NOT_IMPLEMENTED_ERROR_MSG);
    }
}
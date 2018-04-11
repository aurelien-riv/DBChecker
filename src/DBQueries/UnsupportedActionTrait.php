<?php

namespace DBChecker\DBQueries;

use BadMethodCallException;

trait UnsupportedActionTrait
{
    public function getUniqueIndexes($table)
    {
        throw new BadMethodCallException("This method is not available for this database");
    }

    public function getDuplicateForColumnsWithCount($table, $columns)
    {
        throw new BadMethodCallException("This method is not available for this database");
    }

    public function getTableLargerThanMb(int $mb) : \PDOStatement
    {
        throw new BadMethodCallException("This method is not available for this database");
    }

    public function getRandomValuesConcatenated(string $table, int $limit) : \PDOStatement
    {
        throw new BadMethodCallException("This method is not available for this database");
    }

    public function getTableNames()
    {
        throw new BadMethodCallException("This method is not available for this database");
    }

    public function getTableDataSha1sum($table)
    {
        throw new BadMethodCallException("This method is not available for this database");
    }

    public function getTableSchemaSha1sum($table) : string
    {
        throw new BadMethodCallException("This method is not available for this database");
    }

    public function getDistinctValuesWithJoinColumnsWithoutNulls($table, $columns, $innerJoinColumns) : \PDOStatement
    {
        throw new BadMethodCallException("This method is not available for this database");
    }

    public function getPKs($table)
    {
        throw new BadMethodCallException("This method is not available for this database");
    }

    public function getColumnNamesWithTableName()
    {
        throw new BadMethodCallException("This method is not available for this database");
    }

    public function getDistantTableAndColumnFromTableAndColumnFK($table, $column)
    {
        throw new BadMethodCallException("This method is not available for this database");
    }

    public function getFks()
    {
        throw new BadMethodCallException("This method is not available for this database");
    }

    public function getColumnNamesInTable($table)
    {
        throw new BadMethodCallException("This method is not available for this database");
    }

    public function getDistinctValuesWithoutNulls($table, $columns)
    {
        throw new BadMethodCallException("This method is not available for this database");
    }

    public function getValue($table, $column, $value)
    {
        throw new BadMethodCallException("This method is not available for this database");
    }
}
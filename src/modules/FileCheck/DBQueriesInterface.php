<?php

namespace DBChecker\modules\FileCheck;

interface DBQueriesInterface extends \DBChecker\DBQueries\DBQueriesInterface
{
    /**
     * @param string   $table
     * @param string[] $columns
     * @param string[] $innerJoinColumns
     * @return bool|\PDOStatement
     */
    public function getDistinctValuesWithJoinColumnsWithoutNulls($table, $columns, $innerJoinColumns) : \PDOStatement;
}
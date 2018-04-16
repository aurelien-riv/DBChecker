<?php

namespace DBChecker\modules\RelCheck;

interface DBQueriesInterface
{
    public function getName() : string;

    public function getTableNames() : array;

    public function getFks() : array;

    public function getColumnNamesInTable(string $table) : array;

    public function getDistinctValuesWithoutNulls(string $table, array $columns) : array;

    public function getValue(string $table, string $column, $value);
}
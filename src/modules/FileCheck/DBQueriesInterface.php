<?php

namespace DBChecker\modules\FileCheck;

interface DBQueriesInterface
{
    public function getName() : string;

    public function getDistinctValuesWithJoinColumnsWithoutNulls(string $table, array $columns, array $innerJoinColumns, int $limit, int $offset) : array;
}
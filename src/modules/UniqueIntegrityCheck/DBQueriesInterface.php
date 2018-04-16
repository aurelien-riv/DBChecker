<?php

namespace DBChecker\modules\UniqueIntegrityCheck;

interface DBQueriesInterface
{
    public function getName() : string;

    public function getTableNames() : array;

    public function getUniqueIndexes(string $table);

    public function getDuplicateForColumnsWithCount(string $table, string $columns) : array;
}
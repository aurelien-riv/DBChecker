<?php

namespace DBChecker\modules\PwnedAccountsDetect;

interface DBQueriesInterface
{
    public function getName() : string;

    public function getDistinctValuesWithoutNulls(string $table, array $columns) : array;
}
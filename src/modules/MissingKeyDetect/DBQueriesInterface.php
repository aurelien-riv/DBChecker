<?php

namespace DBChecker\modules\MissingKeyDetect;

interface DBQueriesInterface
{
    public function getName() : string;

    public function getPKs(string $table) : array;

    public function getDistantTableAndColumnFromTableAndColumnFK(string $table, string $column) : ?array;
}
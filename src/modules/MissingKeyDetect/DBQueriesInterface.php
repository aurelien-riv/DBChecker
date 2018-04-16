<?php

namespace DBChecker\modules\MissingKeyDetect;

interface DBQueriesInterface
{
    public function getName() : string;

    public function getPKs(string $table) : array;

    /**
     * @param string $table
     * @param string $column
     * @return bool|\PDOStatement
     * Get the table name and the column name on the other side of a foreign key relation
     */
    public function getDistantTableAndColumnFromTableAndColumnFK(string $table, string $column);
}
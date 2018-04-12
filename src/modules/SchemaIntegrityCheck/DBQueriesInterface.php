<?php

namespace DBChecker\modules\SchemaIntegrityCheck;

interface DBQueriesInterface extends \DBChecker\DBQueries\DBQueriesInterface
{
    public function getTableNames() : \PDOStatement;

    /**
     * @param $table
     * @return string
     */
    public function getTableSchemaSha1sum($table) : string;
}
<?php

namespace DBChecker\modules\SchemaIntegrityCheck;

interface DBQueriesInterface extends \DBChecker\DBQueries\DBQueriesInterface
{
    /**
     * @return bool|\PDOStatement
     */
    public function getTableNames();

    /**
     * @param $table
     * @return string
     */
    public function getTableSchemaSha1sum($table) : string;
}
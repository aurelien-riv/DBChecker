<?php

namespace DBChecker\modules\SchemaIntegrityCheck;

interface DBQueriesInterface
{
    public function getName() : string;

    public function getTableNames() : array;

    public function getTableSchemaSha1sum(string $table) : string;
}
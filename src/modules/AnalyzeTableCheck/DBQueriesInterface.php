<?php

namespace DBChecker\modules\AnalyzeTableCheck;

interface DBQueriesInterface extends \DBChecker\DBQueries\DBQueriesInterface
{
    public function getTableNames() : \PDOStatement;

    public function analyzeTable(string $table) : \PDOStatement;
}
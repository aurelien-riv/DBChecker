<?php

namespace DBChecker\modules\AnalyzeTableCheck;

interface DBQueriesInterface
{
    public function getName() : string;

    public function getTableNames() : array;

    public function analyzeTable(string $table) : \stdClass;
}
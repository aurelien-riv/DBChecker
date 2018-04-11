<?php

namespace DBChecker\modules\MissingCompressionDetect;

interface DBQueriesInterface extends \DBChecker\DBQueries\DBQueriesInterface
{
    public function supportsTablespaceCompression() : bool;

    public function isTableCompressed(string $table) : bool;

    public function getTableLargerThanMb(int $minSize_MB) : \PDOStatement;

    public function getRandomValuesConcatenated(string $table, int $limit) : \PDOStatement;
}
<?php

namespace DBChecker\modules\MissingCompressionDetect;

interface DBQueriesInterface
{
    public function getName() : string;

    public function supportsTablespaceCompression() : bool;

    public function isTableCompressed(string $table) : bool;

    public function getTableLargerThanMb(int $minSize_MB) : array;

    public function getRandomValuesConcatenated(string $table, int $limit) : string;
}
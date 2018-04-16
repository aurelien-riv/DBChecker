<?php

namespace DBChecker\modules\DataIntegrityCheck;

interface DBQueriesInterface
{
    public function getName() : string;

    public function getTableNames() : array;

    /**
     * @param  string $table The table name
     * @return string The Sha1 sum of the concatenation of the data from all the columns and then all the rows.
     * The order of the concatenation matters as a change in the concatenation would change the checksum.
     */
    public function getTableDataSha1sum(string $table) : string;
}
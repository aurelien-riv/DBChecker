<?php

namespace DBChecker\modules\MissingCompressionDetect;

class DuplicateCompressionMatch extends CompressionIssueMatch
{
    public function getMessage() : string
    {
        return "{$this->dbName} > {$this->table} Both the table and the data are compressed\n";
    }
}
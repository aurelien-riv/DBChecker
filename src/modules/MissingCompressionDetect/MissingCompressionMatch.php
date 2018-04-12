<?php

namespace DBChecker\modules\MissingCompressionDetect;

class MissingCompressionMatch extends CompressionIssueMatch
{
    public function getMessage() : string
    {
        return "{$this->dbName} > {$this->table} could be smaller with compression enabled\n";
    }
}
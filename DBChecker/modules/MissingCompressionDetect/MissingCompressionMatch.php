<?php

namespace DBChecker\modules\MissingCompressionDetect;

class MissingCompressionMatch extends CompressionIssueMatch
{
    public function __toString()
    {
        return $this->getMessage();
    }

    public function getMessage()
    {
        return "{$this->dbName} > {$this->table} could be smaller with compression enabled\n";
    }
}
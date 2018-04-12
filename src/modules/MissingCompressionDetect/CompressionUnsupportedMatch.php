<?php

namespace DBChecker\modules\MissingCompressionDetect;

class CompressionUnsupportedMatch extends CompressionIssueMatch
{
    public function getMessage() : string
    {
        return "{$this->dbName} > {$this->table} could be smaller with compression enabled,
            but your engine doesn't support compression\n";
    }
}
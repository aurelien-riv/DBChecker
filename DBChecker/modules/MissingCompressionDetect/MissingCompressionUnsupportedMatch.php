<?php

namespace DBChecker\modules\MissingCompressionDetect;

class MissingCompressionUnsupportedMatch extends MissingCompressionMatch
{
    public function getMessage()
    {
        return "{$this->dbName} > {$this->table} could be smaller with compression enabled,
            but your engine doesn't support compression\n";
    }
}
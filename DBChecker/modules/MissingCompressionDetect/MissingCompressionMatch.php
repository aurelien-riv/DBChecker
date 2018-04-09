<?php

namespace DBChecker\modules\MissingCompressionDetect;

use DBChecker\AbstractMatch;

class MissingCompressionMatch extends AbstractMatch
{
    protected $table;

    public function __construct($dbName, $table)
    {
        parent::__construct($dbName);
        $this->table = $table;
    }

    public function __toString()
    {
        return $this->getMessage();
    }

    public function getMessage()
    {
        return "{$this->dbName} > {$this->table} could be smaller with compression enabled\n";
    }
}
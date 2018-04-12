<?php

namespace DBChecker\modules\DataIntegrityCheck;

use DBChecker\AbstractMatch;

class DataIntegrityCheckMatch extends AbstractMatch
{
    private $table;
    private $checksum;

    public function __construct($dbName, $table, $checksum)
    {
        parent::__construct($dbName);
        $this->table            = $table;
        $this->checksum         = $checksum;
    }

    public function getMessage() : string
    {
        return "{$this->dbName} > {$this->table} : checksum does not match ({$this->checksum})\n";
    }

    #region getters
    public function getTable()
    {
        return $this->table;
    }
    #endregion
}
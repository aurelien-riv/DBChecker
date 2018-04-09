<?php

namespace DBChecker\modules\SchemaIntegrityCheck;

use DBChecker\AbstractMatch;

class SchemaIntegrityCheckMatch extends AbstractMatch
{
    private $table;
    private $checksum;

    public function __construct($dbName, $table, $checksum)
    {
        parent::__construct($dbName);
        $this->table            = $table;
        $this->checksum         = $checksum;
    }

    public function __toString()
    {
        return (string) $this->getMessage();
    }

    public function getMessage()
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

<?php

namespace DBChecker\modules\DataIntegrityCheck;

class DataIntegrityCheckMatch
{
    private $table;
    private $checksum;

    public function __construct($table, $checksum)
    {
        $this->table            = $table;
        $this->checksum         = $checksum;
    }

    public function __toString()
    {
        return (string) $this->getMessage();
    }

    public function getMessage()
    {
        return "{$this->table} : checksum does not match ({$this->checksum})\n";
    }

    #region getters
    public function getTable()
    {
        return $this->table;
    }
    #endregion
}
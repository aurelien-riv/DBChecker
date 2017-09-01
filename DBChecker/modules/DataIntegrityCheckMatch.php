<?php

namespace DBChecker;

class DataIntegrityCheckMatch
{
    private $table;

    public function __construct($table)
    {
        $this->table    = $table;
    }

    public function __toString()
    {
        return (string) $this->getMessage();
    }

    public function getMessage()
    {
        return "{$this->table} : checksum does not match\n";
    }

    #region getters
    public function getTable()
    {
        return $this->table;
    }
    #endregion
}
<?php

namespace DBChecker\modules\RelCheck;

class TableNotFoundMatch
{
    protected $table;

    public function __construct($table)
    {
        $this->table = $table;
    }

    public function __toString()
    {
        return (string) $this->getMessage();
    }

    public function getMessage()
    {
        return "{$this->table} doesn't exist\n";
    }

    #region getters
    public function getTable()
    {
        return $this->table;
    }
    #endregion
}
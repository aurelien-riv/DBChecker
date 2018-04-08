<?php

namespace DBChecker\modules\RelCheck;

use DBChecker\AbstractMatch;

class TableNotFoundMatch extends AbstractMatch
{
    protected $table;

    public function __construct($dbName, $table)
    {
        parent::__construct($dbName);
        $this->table = $table;
    }

    public function __toString()
    {
        return (string) $this->getMessage();
    }

    public function getMessage()
    {
        return "{$this->dbName} > {$this->table} doesn't exist\n";
    }

    #region getters
    public function getTable()
    {
        return $this->table;
    }
    #endregion
}
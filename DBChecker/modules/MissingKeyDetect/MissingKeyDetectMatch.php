<?php

namespace DBChecker\modules\MissingKeyDetect;

use DBChecker\AbstractMatch;

class MissingKeyDetectMatch extends AbstractMatch
{
    private $table;
    private $column;

    public function __construct($dbName, $table, $column)
    {
        parent::__construct($dbName);
        $this->table            = $table;
        $this->column           = $column;
    }

    public function __toString()
    {
        return (string) $this->getMessage();
    }

    public function getMessage()
    {
        return "{$this->dbName} > {$this->table}.{$this->column}\n";
    }

    #region getters
    public function getTable()
    {
        return $this->table;
    }

    public function getColumn()
    {
        return $this->column;
    }

    #endregion
}
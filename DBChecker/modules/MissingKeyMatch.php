<?php

namespace DBChecker;

class MissingKeyMatch
{
    private $table;
    private $column;

    public function __construct($table, $column)
    {
        $this->table            = $table;
        $this->column           = $column;
    }

    public function __toString()
    {
        return (string) $this->getMessage();
    }

    public function getMessage()
    {
        return "{$this->table}.{$this->column}\n";
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
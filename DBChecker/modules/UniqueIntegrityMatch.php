<?php

namespace DBChecker;

class UniqueIntegrityMatch
{
    private $table;
    private $columns;
    private $values;

    public function __construct($table, $columns, $values)
    {
        $this->table   = $table;
        $this->columns = $columns;
        $this->values  = $values;
    }

    public function __toString()
    {
        return (string) $this->getMessage();
    }

    public function getMessage()
    {
        return "{$this->table}.{{$this->columns}} # {$this->values}\n";
    }

    #region getters
    public function getTable()
    {
        return $this->table;
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function getValues()
    {
        return $this->values;
    }
    #endregion
}
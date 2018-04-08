<?php

namespace DBChecker\modules\RelCheck;

use DBChecker\AbstractMatch;

class RelCheckMatch extends AbstractMatch
{
    private $table;
    private $column;
    private $referencedTable;
    private $referencedColumn;
    private $value;

    public function __construct($dbName, $table, $column, $referencedTable, $referencedColumn, $value)
    {
        parent::__construct($dbName);
        $this->table            = $table;
        $this->column           = $column;
        $this->referencedTable  = $referencedTable;
        $this->referencedColumn = $referencedColumn;
        $this->value            = $value;
    }

    public function __toString()
    {
        return (string) $this->getMessage();
    }

    public function getMessage()
    {
        return "{$this->dbName} > {$this->table}.{$this->column} -> {$this->referencedTable}.{$this->referencedColumn} # {$this->value}\n";
    }

    public function toSQLDelete()
    {
        return "DELETE FROM {$this->getTable()} WHERE {$this->getColumn()} = '{$this->value}';\n";
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

    public function getReferencedTable()
    {
        return $this->referencedTable;
    }

    public function getReferencedColumn()
    {
        return $this->referencedColumn;
    }

    public function getValue()
    {
        return $this->value;
    }
    #endregion
}
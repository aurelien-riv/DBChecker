<?php

namespace DBChecker\modules\RelCheck;

use DBChecker\AbstractMatch;
use DBChecker\BaseMatch\ColumnTrait;
use DBChecker\BaseMatch\TableTrait;

class RelCheckMatch extends AbstractMatch
{
    use TableTrait;
    use ColumnTrait;
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

    public function getMessage() : string
    {
        return "{$this->dbName} > {$this->table}.{$this->column} -> {$this->referencedTable}.{$this->referencedColumn} # {$this->value}\n";
    }

    #region getters
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
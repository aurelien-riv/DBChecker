<?php

namespace DBChecker\modules\RelCheck;

class ColumnNotFoundMatch extends TableNotFoundMatch
{
    private $column;

    public function __construct($dbName, $table, $column)
    {
        parent::__construct($dbName, $table);
        $this->column = $column;
    }

    public function getMessage()
    {
        return "{$this->dbName} > {$this->getTable()}.{$this->getColumn()} doesn't exist\n";
    }

    #region getters
    public function getColumn()
    {
        return $this->column;
    }
    #endregion
}
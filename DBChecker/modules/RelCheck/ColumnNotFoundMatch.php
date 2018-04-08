<?php

namespace DBChecker\modules\RelCheck;

class ColumnNotFoundMatch extends TableNotFoundMatch
{
    private $column;

    public function __construct($table, $column)
    {
        parent::__construct($table);
        $this->column = $column;
    }

    public function getMessage()
    {
        return "{$this->getTable()}.{$this->getColumn()} doesn't exist\n";
    }

    #region getters
    public function getColumn()
    {
        return $this->column;
    }
    #endregion
}
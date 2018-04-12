<?php

namespace DBChecker\modules\RelCheck;

use DBChecker\BaseMatch\ColumnTrait;

class ColumnNotFoundMatch extends TableNotFoundMatch
{
    use ColumnTrait;

    public function __construct($dbName, $table, $column)
    {
        parent::__construct($dbName, $table);
        $this->column = $column;
    }

    public function getMessage() : string
    {
        return "{$this->dbName} > {$this->getTable()}.{$this->getColumn()} doesn't exist\n";
    }
}
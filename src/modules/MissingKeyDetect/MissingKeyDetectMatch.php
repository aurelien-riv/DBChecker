<?php

namespace DBChecker\modules\MissingKeyDetect;

use DBChecker\AbstractMatch;
use DBChecker\BaseMatch\ColumnTrait;
use DBChecker\BaseMatch\TableTrait;

class MissingKeyDetectMatch extends AbstractMatch
{
    use TableTrait;
    use ColumnTrait;

    public function __construct($dbName, $table, $column)
    {
        parent::__construct($dbName);
        $this->table  = $table;
        $this->column = $column;
    }

    public function getMessage() : string
    {
        return "{$this->dbName} > {$this->table}.{$this->column}\n";
    }
}
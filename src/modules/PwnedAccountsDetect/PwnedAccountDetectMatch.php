<?php

namespace DBChecker\modules\PwnedAccountsDetect;

use DBChecker\AbstractMatch;
use DBChecker\BaseMatch\ColumnTrait;
use DBChecker\BaseMatch\TableTrait;

class PwnedAccountDetectMatch extends AbstractMatch
{
    use TableTrait;
    use ColumnTrait;

    private $value;

    public function __construct($dbName, $table, $column, $value)
    {
        parent::__construct($dbName);
        $this->table = $table;
        $this->column = $column;
        $this->value = $value;
    }

    public function getMessage() : string
    {
        return "{$this->dbName} > {$this->table}.{$this->column} Account {$this->value} might have been pwned\n";
    }
}
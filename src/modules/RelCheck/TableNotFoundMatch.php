<?php

namespace DBChecker\modules\RelCheck;

use DBChecker\AbstractMatch;
use DBChecker\BaseMatch\TableTrait;

class TableNotFoundMatch extends AbstractMatch
{
    use TableTrait;

    public function __construct($dbName, $table)
    {
        parent::__construct($dbName);
        $this->table = $table;
    }

    public function getMessage() : string
    {
        return "{$this->dbName} > {$this->table} doesn't exist\n";
    }
}
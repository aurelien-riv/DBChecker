<?php

namespace DBChecker\modules\AnalyzeTableCheck;

use DBChecker\AbstractMatch;
use DBChecker\BaseMatch\TableTrait;

class AnalyzeTableCheckMatch extends AbstractMatch
{
    use TableTrait;

    private $message;

    public function __construct($dbName, $table, $message)
    {
        parent::__construct($dbName);
        $this->table = $table;
        $this->message = $message;
    }

    public function getMessage() : string
    {
        return "{$this->dbName} > {$this->table} is corrupted ({$this->message}).\n";
    }
}
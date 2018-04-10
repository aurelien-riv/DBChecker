<?php

namespace DBChecker\modules\MissingCompressionDetect;

use DBChecker\AbstractMatch;

class DuplicateCompressionMatch extends AbstractMatch
{
    protected $table;
    protected $column;

    public function __construct($dbName, $table, $column)
    {
        parent::__construct($dbName);
        $this->table = $table;
        $this->column = $column;
    }

    public function __toString()
    {
        return $this->getMessage();
    }

    public function getMessage()
    {
        return "{$this->dbName} > {$this->table}.{$this->column} Both the table and the data are compressed\n";
    }
}
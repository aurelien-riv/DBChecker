<?php

namespace DBChecker\modules\SchemaIntegrityCheck;

use DBChecker\AbstractMatch;
use DBChecker\BaseMatch\TableTrait;

class SchemaIntegrityCheckMatch extends AbstractMatch
{
    use TableTrait;
    private $checksum;

    public function __construct($dbName, $table, $checksum)
    {
        parent::__construct($dbName);
        $this->table    = $table;
        $this->checksum = $checksum;
    }

    public function getMessage() : string
    {
        return "{$this->dbName} > {$this->table} : checksum does not match ({$this->checksum})\n";
    }
}

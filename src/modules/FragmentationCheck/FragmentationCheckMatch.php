<?php

namespace DBChecker\modules\FragmentationCheck;

use DBChecker\AbstractMatch;
use DBChecker\BaseMatch\TableTrait;

class FragmentationCheckMatch extends AbstractMatch
{
    use TableTrait;
    private $fragmentation;

    public function __construct($dbName, $table, $fragmentation)
    {
        parent::__construct($dbName);
        $this->table = $table;
        $this->fragmentation = $fragmentation;
    }

    public function getMessage() : string
    {
        return "{$this->dbName} > {$this->table} is fragmented ({$this->fragmentation}%)\n";
    }
}
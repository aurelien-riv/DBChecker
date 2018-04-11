<?php

namespace DBChecker\modules\FragmentationCheck;

use DBChecker\AbstractMatch;

class FragmentationCheckMatch extends AbstractMatch
{
    private $fragmentation;
    private $table;

    public function __construct($dbName, $table, $fragmentation)
    {
        parent::__construct($dbName);
        $this->table = $table;
        $this->fragmentation = $fragmentation;
    }

    public function getMessage()
    {
        return "{$this->dbName} > {$this->table} is fragmented ({$this->fragmentation}%)\n";
    }
}
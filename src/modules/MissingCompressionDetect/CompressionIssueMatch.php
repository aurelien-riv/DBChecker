<?php

namespace DBChecker\modules\MissingCompressionDetect;

use DBChecker\AbstractMatch;
use DBChecker\BaseMatch\TableTrait;

abstract class CompressionIssueMatch extends AbstractMatch
{
    use TableTrait;

    public function __construct($dbName, $table)
    {
        parent::__construct($dbName);
        $this->table = $table;
    }
}
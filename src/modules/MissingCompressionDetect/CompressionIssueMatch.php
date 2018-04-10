<?php

namespace DBChecker\modules\MissingCompressionDetect;

use DBChecker\AbstractMatch;

abstract class CompressionIssueMatch extends AbstractMatch
{
    protected $table;

    public function __construct($dbName, $table)
    {
        parent::__construct($dbName);
        $this->table = $table;
    }

    public function __toString()
    {
        return $this->getMessage();
    }
}
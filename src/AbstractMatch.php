<?php

namespace DBChecker;

abstract class AbstractMatch
{
    protected $dbName;

    public function __construct($dbName)
    {
        $this->dbName = $dbName;
    }

    public function getDbName()
    {
        return $this->dbName;
    }

    public function __toString()
    {
        return $this->getMessage();
    }

    public abstract function getMessage() : string;
}
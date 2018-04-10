<?php

namespace DBChecker;

abstract class AbstractMatch
{
    protected $dbName;

    public function __construct($dbName)
    {
        $this->dbName = $dbName;
    }

    public function __toString()
    {
        return $this->getMessage();
    }

    /**
     * @return string
     */
    public abstract function getMessage();
}
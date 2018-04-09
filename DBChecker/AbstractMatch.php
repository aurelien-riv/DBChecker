<?php

namespace DBChecker;

abstract class AbstractMatch
{
    protected $dbName;

    public function __construct($dbName)
    {
        $this->dbName = $dbName;
    }

    /**
     * @return string
     */
    public abstract function getMessage();
}
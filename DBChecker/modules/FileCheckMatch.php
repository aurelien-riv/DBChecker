<?php

namespace DBChecker;

class FileCheckMatch
{
    private $table;
    private $column;
    private $basePath;
    private $value;

    public function __construct($table, $column, $basePath, $value)
    {
        $this->table    = $table;
        $this->column   = $column;
        $this->basePath = $basePath;
        $this->value    = $value;
    }

    public function __toString()
    {
        return (string) $this->getMessage();
    }

    public function getMessage()
    {
        return "{$this->table}.{$this->column} : {$this->basePath}/{$this->value} : no such file or directory\n";
    }

    #region getters
    public function getTable()
    {
        return $this->table;
    }

    public function getColumn()
    {
        return $this->column;
    }

    public function getBasePath()
    {
        return $this->basePath;
    }

    public function getValue()
    {
        return $this->value;
    }
    #endregion
}
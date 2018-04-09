<?php

namespace DBChecker\modules\FileCheck;

use DBChecker\AbstractMatch;

class FileCheckMatch extends AbstractMatch
{
    protected $table;
    protected $columns;
    protected $path;

    public function __construct($dbName, $table, $columns, $path)
    {
        parent::__construct($dbName);
        $this->table   = $table;
        $this->columns = $columns;
        $this->path    = $path;
    }

    public function __toString()
    {
        return (string) $this->getMessage();
    }

    public function getMessage()
    {
        $cols = '';
        foreach ($this->columns as $column => $value)
        {
            $cols .= "$column=$value, ";
        }
        $cols = rtrim($cols, ', ');
        return "{$this->dbName} > {$this->table}.{{$cols}} : {$this->path} : no such file or directory\n";
    }

    public function toSQLDelete()
    {
        $cols = '';
        foreach ($this->columns as $column => $value)
        {
            if (strpos($column, '.'))
                throw new \InvalidArgumentException("Joins are not supported yet in this function");

            $cols .= "$column='$value', ";
        }
        $cols = rtrim($cols, ', ');

        return "DELETE FROM {$this->getTable()} "
            . "WHERE $cols;\n";
    }

    #region getters
    public function getTable()
    {
        return $this->table;
    }

    public function getColumn()
    {
        return $this->columns;
    }

    public function getPath()
    {
        return $this->path;
    }
    #endregion
}
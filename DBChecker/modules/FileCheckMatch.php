<?php

namespace DBChecker;

class FileCheckMatch
{
    protected $table;
    protected $columns;
    protected $path;

    public function __construct($table, $columns, $path)
    {
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
        $cols = $this->columns;
        if (is_array($this->columns))
            $cols = implode(',', $this->columns);
        return "{$this->table}.{{$cols}} : {$this->path} : no such file or directory\n";
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
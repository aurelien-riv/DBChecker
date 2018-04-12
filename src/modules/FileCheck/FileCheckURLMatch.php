<?php

namespace DBChecker\modules\FileCheck;

class FileCheckURLMatch extends FileCheckMatch
{
    protected $header0;

    public function __construct($dbName, $table, $columns, $path, $header0)
    {
        parent::__construct($dbName, $table, $columns, $path);
        $this->header0 = $header0;
    }

    public function getMessage() : string
    {
        $cols = $this->columns;
        if (is_array($this->columns))
            $cols = implode(',', $this->columns);
        return "{$this->dbName} > {$this->table}.{{$cols}} : {$this->path} : {$this->header0}\n";
    }
}
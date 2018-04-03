<?php

namespace DBChecker;

require_once __DIR__.'/FileCheckMatch.php';

class FileCheckURLMatch extends FileCheckMatch
{
    protected $header0;

    public function __construct($table, $columns, $path, $header0)
    {
        parent::__construct($table, $columns, $path);
        $this->header0 = $header0;
    }

    public function getMessage()
    {
        $cols = $this->columns;
        if (is_array($this->columns))
            $cols = implode(',', $this->columns);
        return "{$this->table}.{{$cols}} : {$this->path} : {$this->header0}\n";
    }
}
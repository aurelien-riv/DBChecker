<?php

namespace DBChecker\modules\UniqueIntegrityCheck;

use DBChecker\AbstractMatch;
use DBChecker\BaseMatch\TableTrait;

class UniqueIntegrityCheckMatch extends AbstractMatch
{
    use TableTrait;
    private $values;
    private $count;

    public function __construct($dbName, $table, $columns, $results)
    {
        parent::__construct($dbName);
        $this->table = $table;
        foreach (mb_split(',', $columns) as $column)
        {
            $this->values[$column] = $results->{$column};
        }
        $this->count  = $results->__count__;
    }

    public function getMessage() : string
    {
        $data = '';
        foreach ($this->values as $column => $value)
        {
            if ($value === null)
                $value = '∅';
            else if (is_string($value))
                $value = "'$value'";
            $data .= "$column:$value, ";
        }
        $data = rtrim($data, ", ");
        return "$this->dbName} > {$this->table} {{$data}} (count:{$this->count})\n";
    }

    #region getters
    public function getValues()
    {
        return $this->values;
    }

    public function getCount()
    {
        return $this->count;
    }
    #endregion
}
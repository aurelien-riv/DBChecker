<?php

namespace DBChecker;

class UniqueIntegrityMatch
{
    private $table;
    private $values;
    private $count;

    public function __construct($table, $columns, $results)
    {
        $this->table   = $table;
        foreach (mb_split(',', $columns) as $column)
        {
            $this->values[$column] = $results->{$column};
        }
        $this->count  = $results->__count__;
    }

    public function __toString()
    {
        return (string) $this->getMessage();
    }

    public function getMessage()
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
        return "{$this->table} {{$data}} (count:{$this->count})\n";
    }

    #region getters
    public function getTable()
    {
        return $this->table;
    }

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
<?php

namespace DBChecker\BaseMatch;

trait ColumnTrait
{
    protected $column;

    public function getColumn()
    {
        return $this->column;
    }
}
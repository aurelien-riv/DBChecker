<?php

namespace DBChecker\BaseMatch;

trait TableTrait
{
    protected $table;

    public function getTable() : string
    {
        return $this->table;
    }
}
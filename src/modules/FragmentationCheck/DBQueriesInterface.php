<?php

namespace DBChecker\modules\FragmentationCheck;

interface DBQueriesInterface extends \DBChecker\DBQueries\DBQueriesInterface
{
    public function getFragmentedTables() : \PDOStatement;
}
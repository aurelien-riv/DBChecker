<?php

namespace DBChecker\modules\FragmentationCheck;

interface DBQueriesInterface
{
    public function getName() : string;

    public function getFragmentedTables() : array;
}
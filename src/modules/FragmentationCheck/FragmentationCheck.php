<?php

namespace DBChecker\modules\FragmentationCheck;

use DBChecker\InputModules\AbstractDBAL;
use DBChecker\ModuleWorkerInterface;

class FragmentationCheck implements ModuleWorkerInterface
{
    public function run(AbstractDBAL $dbal)
    {
        foreach ($dbal->getFragmentedTables() as $table)
        {
            yield new FragmentationCheckMatch($dbal->getName(), $table->TABLE_NAME, $table->FRAGMENTATION);
        }
    }
}
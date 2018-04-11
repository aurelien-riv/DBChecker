<?php

namespace DBChecker\modules\FragmentationCheck;

use DBChecker\DBQueries\AbstractDbQueries;
use DBChecker\ModuleWorkerInterface;

class FragmentationCheck implements ModuleWorkerInterface
{
    public function run(AbstractDbQueries $dbQueries)
    {
        $tables = $dbQueries->getFragmentedTables()->fetchAll(\PDO::FETCH_OBJ);
        foreach ($tables as $table)
        {
            yield new FragmentationCheckMatch($dbQueries->getName(), $table->TABLE_NAME, $table->FRAGMENTATION);
        }
    }
}
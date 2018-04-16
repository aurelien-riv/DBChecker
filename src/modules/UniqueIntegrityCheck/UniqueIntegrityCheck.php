<?php

namespace DBChecker\modules\UniqueIntegrityCheck;

use DBChecker\DBAL\AbstractDBAL;
use DBChecker\ModuleWorkerInterface;

class UniqueIntegrityCheck implements ModuleWorkerInterface
{
    public function run(AbstractDBAL $dbal)
    {
        foreach ($dbal->getTableNames() as $table)
        {
            $indexColumns = $dbal->getUniqueIndexes($table);
            foreach ($indexColumns as $columns)
            {
                $resultset = $dbal->getDuplicateForColumnsWithCount($table, $columns);
                foreach ($resultset as $result)
                {
                    yield new UniqueIntegrityCheckMatch($dbal->getName(), $table, $columns, $result);
                }
            }
        }
    }
}
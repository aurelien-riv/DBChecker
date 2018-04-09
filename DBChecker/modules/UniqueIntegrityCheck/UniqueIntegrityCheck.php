<?php

namespace DBChecker\modules\UniqueIntegrityCheck;

use DBChecker\DBQueries\AbstractDbQueries;

class UniqueIntegrityCheck
{
    public function run(AbstractDbQueries $dbQueries)
    {
        $tables = $dbQueries->getTableNames()->fetchAll(\PDO::FETCH_COLUMN);
        foreach ($tables as $table)
        {
            $indexColumns = $dbQueries->getUniqueIndexes($table)->fetchAll(\PDO::FETCH_COLUMN);
            foreach ($indexColumns as $columns)
            {
                $resultset = $dbQueries->getDuplicateForColumnsWithCount($table, $columns)->fetchAll(\PDO::FETCH_OBJ);
                foreach ($resultset as $result)
                {
                    yield new UniqueIntegrityCheckMatch($dbQueries->getName(), $table, $columns, $result);
                }
            }
        }
    }
}
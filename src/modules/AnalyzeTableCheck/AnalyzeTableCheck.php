<?php

namespace DBChecker\modules\AnalyzeTableCheck;

use DBChecker\DBQueries\AbstractDbQueries;
use DBChecker\ModuleWorkerInterface;

class AnalyzeTableCheck implements ModuleWorkerInterface
{
    public function run(AbstractDbQueries $dbQueries)
    {
        $tables = $dbQueries->getTableNames()->fetchAll(\PDO::FETCH_COLUMN);
        foreach ($tables as $table)
        {
            $analyze = $dbQueries->analyzeTable($table)->fetch(\PDO::FETCH_OBJ);
            if ($analyze->Msg_type !== 'status')
            {
                yield new AnalyzeTableCheckMatch($dbQueries->getName(), $table, $analyze->Msg_text);
            }
        }
    }
}
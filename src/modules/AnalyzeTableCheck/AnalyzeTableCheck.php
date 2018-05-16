<?php

namespace DBChecker\modules\AnalyzeTableCheck;

use DBChecker\InputModules\AbstractDBAL;
use DBChecker\ModuleWorkerInterface;

class AnalyzeTableCheck implements ModuleWorkerInterface
{
    public function run(AbstractDbal $dbal)
    {
        $tables = $dbal->getTableNames();
        foreach ($tables as $table)
        {
            $analyze = $dbal->analyzeTable($table);
            if ($analyze->Msg_type !== 'status')
            {
                yield new AnalyzeTableCheckMatch($dbal->getName(), $table, $analyze->Msg_text);
            }
        }
    }
}
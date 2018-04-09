<?php

namespace DBChecker\modules\MissingCompressionDetect;

use DBChecker\DBQueries\AbstractDbQueries;
use DBChecker\ModuleWorkerInterface;

class MissingCompressionDetect implements ModuleWorkerInterface
{
    public function run(AbstractDbQueries $dbQueries)
    {
        $engineSupportsCompression = $dbQueries->supportsTablespaceCompression();

        $tables = $dbQueries->getTableNamesFilterByColumnMinOctetLength(1024)->fetchAll(\PDO::FETCH_OBJ);
        foreach ($tables as $table)
        {
            if (! $engineSupportsCompression)
            {
                yield new MissingCompressionUnsupportedMatch($dbQueries->getName(), $table->TABLE_NAME);
                continue;
            }

            if (! $dbQueries->isTableCompressed($table->TABLE_NAME))
            {
                yield new MissingCompressionMatch($dbQueries->getName(), $table->TABLE_NAME);
            }
        }
    }

}
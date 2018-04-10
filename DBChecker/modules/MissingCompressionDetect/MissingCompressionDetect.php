<?php

namespace DBChecker\modules\MissingCompressionDetect;

use DBChecker\DBQueries\AbstractDbQueries;
use DBChecker\ModuleWorkerInterface;

class MissingCompressionDetect implements ModuleWorkerInterface
{
    public function run(AbstractDbQueries $dbQueries)
    {
        $engineSupportsCompression = $dbQueries->supportsTablespaceCompression();

        $schemas = $dbQueries->getTableAndColumnNamesFilterByColumnMinOctetLength(2048)->fetchAll(\PDO::FETCH_OBJ);
        foreach ($schemas as $schema)
        {
            if (!$engineSupportsCompression)
            {
                yield new MissingCompressionUnsupportedMatch($dbQueries->getName(), $schema->TABLE_NAME);
                continue;
            }

            $tableCompressed = $dbQueries->isTableCompressed($schema->TABLE_NAME);
            $dataCompressed = $this->detectCompressedValue($dbQueries, $schema->TABLE_NAME, $schema->COLUMN_NAME);
            if ($dataCompressed !== null)
            {
                if (! $dataCompressed && ! $tableCompressed)
                {
                    yield new MissingCompressionMatch($dbQueries->getName(), $schema->TABLE_NAME);
                }
                else if ($dataCompressed && $tableCompressed)
                {
                    yield new DuplicateCompressionMatch($dbQueries->getName(), $schema->TABLE_NAME, $schema->COLUMN_NAME);
                }
            }
        }
    }

    /**
     * @param AbstractDbQueries $dbQueries
     * @param string            $table
     * @param string            $column
     * @return bool|null
     * Returns null if no data, true if the data is already compressed (= a compression reduces the size of less than
     * 80%), false otherwise.
     */
    private function detectCompressedValue(AbstractDbQueries $dbQueries, string $table, string $column) : ?bool
    {
        $data = $dbQueries->getRandomValuesInColumnConcatenated($table, $column, 15)->fetchAll(\PDO::FETCH_COLUMN);
        if ($data)
        {
            $compressedDataLen = strlen(gzcompress($data[0]));
            if ($compressedDataLen > strlen($data[0]) * 0.80)
            {
                return true;
            }
            return false;
        }
        return null;
    }
}
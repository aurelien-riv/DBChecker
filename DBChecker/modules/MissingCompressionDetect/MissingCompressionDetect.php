<?php

namespace DBChecker\modules\MissingCompressionDetect;

use DBChecker\DBQueries\AbstractDbQueries;
use DBChecker\ModuleWorkerInterface;
use Symfony\Component\Filesystem\Exception\InvalidArgumentException;

class MissingCompressionDetect implements ModuleWorkerInterface
{
    public function run(AbstractDbQueries $dbQueries)
    {
        $engineSupportsCompression = $dbQueries->supportsTablespaceCompression();

        $tables = $dbQueries->getTableLargerThanMb(3)->fetchAll(\PDO::FETCH_OBJ);
        foreach ($tables as $table)
        {
            $tableCompressed = $dbQueries->isTableCompressed($table->TABLE_NAME);
            $needsCompression = $this->needsCompression($dbQueries, $table->TABLE_NAME);
            yield from $this->yieldOnError($dbQueries, $table->TABLE_NAME, $tableCompressed, $needsCompression, $engineSupportsCompression);
        }
    }

    public function yieldOnError(DBQueriesInterface $dbQueries, $tableName, $isCompressed, $needsCompression, $canCompress)
    {
        if ($needsCompression)
        {
            if (! $canCompress)
            {
                yield new MissingCompressionUnsupportedMatch($dbQueries->getName(), $tableName);
            }
            else if (! $isCompressed)
            {
                yield new MissingCompressionMatch($dbQueries->getName(), $tableName);
            }
        }
        else if ($isCompressed)
        {
            yield new DuplicateCompressionMatch($dbQueries->getName(), $tableName);
        }
    }

    /**
     * @param DBQueriesInterface $dbQueries
     * @param string            $table
     * @return bool|null
     * Returns true if the data is significantly smaller once compressed, false otherwise.
     */
    private function needsCompression(DBQueriesInterface $dbQueries, string $table) : bool
    {
        $data = $dbQueries->getRandomValuesConcatenated($table, 15)->fetchAll(\PDO::FETCH_COLUMN);
        if (empty($data))
        {
            throw new InvalidArgumentException("Table has no data");
        }
        return ! (strlen(gzcompress($data[0])) > strlen($data[0]) * 0.80);
    }
}
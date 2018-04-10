<?php

namespace DBChecker\modules\MissingCompressionDetect;

use DBChecker\DBQueries\AbstractDbQueries;
use DBChecker\ModuleWorkerInterface;
use Symfony\Component\Filesystem\Exception\InvalidArgumentException;

class MissingCompressionDetect implements ModuleWorkerInterface
{
    private $config;

    public function __construct(MissingCompressionDetectModule $module)
    {
        $this->config = $module->getConfig();
    }

    public function run(AbstractDbQueries $dbQueries)
    {
        $engineSupportsCompression = $dbQueries->supportsTablespaceCompression();
        $dbName = $dbQueries->getName();

        $tables = $dbQueries->getTableLargerThanMb($this->config['largeTableSize'])->fetchAll(\PDO::FETCH_OBJ);
        foreach ($tables as $table)
        {
            $tableName = $table->TABLE_NAME;
            $tableCompressed = $dbQueries->isTableCompressed($tableName);
            $needsCompression = $this->needsCompression($dbQueries, $tableName);
            yield from $this->yieldOnError($dbName, $tableName, $tableCompressed, $needsCompression, $engineSupportsCompression);
        }
    }

    public function yieldOnError(string $dbName, string $tableName, bool $isCompressed, bool $needsCompression, bool $canCompress)
    {
        if ($isCompressed && ! $canCompress)
        {
            throw new \LogicException("A database cannot be compressed without compression support");
        }
        if ($needsCompression)
        {
            if (! $canCompress)
            {
                yield new CompressionUnsupportedMatch($dbName, $tableName);
            }
            else if (! $isCompressed)
            {
                yield new MissingCompressionMatch($dbName, $tableName);
            }
        }
        else if ($isCompressed)
        {
            yield new DuplicateCompressionMatch($dbName, $tableName);
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
        return ! (strlen(gzcompress($data[0])) > strlen($data[0]) * $this->config['minimalCompressionRatio'] / 100);
    }
}
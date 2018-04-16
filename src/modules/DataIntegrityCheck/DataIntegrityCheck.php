<?php

namespace DBChecker\modules\DataIntegrityCheck;

use DBChecker\DBAL\AbstractDBAL;
use DBChecker\ModuleInterface;
use DBChecker\ModuleWorkerInterface;

/**
 * Compare the checksum of all the data in a table and the value stored in the config file
 */
class DataIntegrityCheck implements ModuleWorkerInterface
{
    private $config;

    public function __construct(ModuleInterface $module)
    {
        $this->config = $module->getConfig();
    }

    public function run(AbstractDBAL $dbal)
    {
        foreach ($this->config['mapping'] as $mapping)
        {
            $table = key($mapping);
            $expectedChecksum = $mapping[key($mapping)];
            $checksum = $dbal->getTableDataSha1sum($table);
            if ($checksum !== $expectedChecksum)
            {
                yield new DataIntegrityCheckMatch($dbal->getName(), $table, $checksum);
            }
        }
    }

    /**
     * @param DBQueriesInterface $dbQueries
     * @return string
     * Proposes a new set of checksums for the configuration file
     */
    public function updateConfig(DBQueriesInterface $dbQueries) : string
    {
        $ret =  "dataintegritycheck:\n";
        $ret .= "  mapping:\n";
        foreach (array_keys($this->config['mapping']) as $table)
        {
            $checksum = $dbQueries->getTableDataSha1sum($table);
            if ($checksum)
            {
                $ret = "    - $table: $checksum\n";
            }
        }
        return $ret;
    }

    /**
     * @param DBQueriesInterface $dbQueries
     * @return string
     */
    public function generateConfig(DBQueriesInterface $dbQueries) : string
    {
        $ret = "dataintegritycheck:\n";
        $ret .= "  mapping:\n";
        foreach ($dbQueries->getTableNames() as $table)
        {
            $checksum = $dbQueries->getTableDataSha1sum($table);
            if ($checksum)
            {
                $ret .= "    - $table: $checksum\n";
            }
        }
        return $ret;
    }
}
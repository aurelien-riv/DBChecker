<?php

namespace DBChecker\modules\DataIntegrityCheck;

use DBChecker\DBQueries\AbstractDbQueries;
use DBChecker\ModuleInterface;

/**
 * Compare the checksum of all the data in a table and the value stored in the config file
 */
class DataIntegrityCheck
{
    private $config;

    public function __construct(ModuleInterface $module)
    {
        $this->config = $module->getConfig();
    }

    public function run(AbstractDbQueries $dbQueries)
    {
        foreach ($this->config['mapping'] as $table => $expectedChecksum)
        {
            $checksum = $dbQueries->getTableDataSha1sum($table);
            if ($checksum !== $expectedChecksum)
                yield new DataIntegrityCheckMatch($dbQueries->getName(), $table, $checksum);
        }
    }

    /**
     * @param AbstractDbQueries $dbQueries
     * Proposes a new set of checksums for the configuration file
     */
    public function updateConfig(AbstractDbQueries $dbQueries)
    {
        echo "datainregritycheck";
        echo "  mapping:";
        foreach (array_keys($this->config['mapping']) as $table)
        {
            $checksum = $dbQueries->getTableDataSha1sum($table);
            if ($checksum)
                echo "    - $table: $checksum\n";
        }
    }

    /**
     * @param AbstractDbQueries $dbQueries
     * Generate a set of checksums for the configuration file
     */
    public function generateConfig(AbstractDbQueries $dbQueries)
    {
        echo "datainregritycheck";
        echo "  mapping:";
        foreach ($dbQueries->getTableNames()->fetchAll(\PDO::FETCH_COLUMN) as $table)
        {
            $checksum = $dbQueries->getTableDataSha1sum($table);
            if ($checksum)
                echo "    - $table: $checksum\n";
        }
    }
}
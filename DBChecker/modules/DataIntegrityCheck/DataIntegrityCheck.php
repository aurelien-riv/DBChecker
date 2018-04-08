<?php

namespace DBChecker\modules\DataIntegrityCheck;

use DBChecker\Config;
use DBChecker\DBQueries\AbstractDbQueries;

/**
 * Compare the checksum of all the data in a table and the value stored in the config file
 */
class DataIntegrityCheck
{
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function run(AbstractDbQueries $dbQueries)
    {
        $settings = $this->config->getDataintegrity();
        if (isset($settings['mapping']))
        {
            foreach ($settings['mapping'] as $table => $expectedChecksum)
            {
                $checksum = $dbQueries->getTableDataSha1sum($table);
                if ($checksum !== $expectedChecksum)
                    yield new DataIntegrityCheckMatch($dbQueries->getName(), $table, $checksum);
            }
        }
    }

    /**
     * @param AbstractDbQueries $dbQueries
     * Proposes a new set of checksums for the configuration file
     * FIXME adapt to yaml yet
     */
    public function updateConfig(AbstractDbQueries $dbQueries)
    {
        foreach ($this->config->getDataintegrity() as $table => $_unused_expectedChecksum)
        {
            $checksum = $dbQueries->getTableDataSha1sum($table);
            if ($checksum)
                echo "$table = $checksum\n";
        }
    }

    /**
     * @param AbstractDbQueries $dbQueries
     * Generate a set of checksums for the configuration file
     * FIXME adapt to yaml yet
     */
    public function generateConfig(AbstractDbQueries $dbQueries)
    {
        foreach ($dbQueries->getTableNames()->fetchAll(\PDO::FETCH_COLUMN) as $table)
        {
            $checksum = $dbQueries->getTableDataSha1sum($table);
            if ($checksum)
                echo "$table = $checksum\n";
        }
    }
}
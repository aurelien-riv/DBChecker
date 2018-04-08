<?php

namespace DBChecker\modules\DataIntegrityCheck;

use DBChecker\Config;

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

    public function run()
    {
        $settings = $this->config->getDataintegrity();
        if (isset($settings['mapping']))
        {
            $queries = $this->config->getQueries();
            foreach ($settings['mapping'] as $table => $expectedChecksum)
            {
                $checksum = $queries->getTableDataSha1sum($table);
                if ($checksum !== $expectedChecksum)
                    yield new DataIntegrityCheckMatch($table, $checksum);
            }
        }
    }

    public function updateConfig()
    {
        $queries = $this->config->getQueries();
        foreach ($this->config->getDataintegrity() as $table => $_unused_expectedChecksum)
        {
            $checksum = $queries->getTableDataSha1sum($table);
            if ($checksum)
                echo "$table = $checksum\n";
        }
    }

    public function generateConfig()
    {
        $queries = $this->config->getQueries();
        foreach ($queries->getTableNames()->fetchAll(\PDO::FETCH_COLUMN) as $table)
        {
            $checksum = $queries->getTableDataSha1sum($table);
            if ($checksum)
                echo "$table = $checksum\n";
        }
    }
}
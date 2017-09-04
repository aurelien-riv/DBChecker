<?php

namespace DBChecker;

require_once 'DataIntegrityCheckMatch.php';

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
        $queries = $this->config->getQueries();
        foreach ($this->config->getDataintegrityConfig() as $table => $expectedChecksum)
        {
            $checksum = $queries->getTableSha1sum($table);
            if ($checksum !== $expectedChecksum)
                yield new DataIntegrityCheckMatch($table);
        }
    }

    public function updateConfig()
    {
        $queries = $this->config->getQueries();
        foreach ($this->config->getDataintegrityConfig() as $table => $_unused_expectedChecksum)
        {
            $checksum = $queries->getTableSha1sum($table);
            if ($checksum)
                echo "$table: $checksum\n";
        }
    }

    public function generateConfig()
    {
        $queries = $this->config->getQueries();
        foreach ($queries->getTableNames()->fetchAll(\PDO::FETCH_COLUMN) as $table)
        {
            $checksum = $queries->getTableSha1sum($table);
            if ($checksum)
                echo "$table: $checksum\n";
        }
    }
}
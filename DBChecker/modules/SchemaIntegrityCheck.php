<?php

namespace DBChecker;

require_once 'SchemaIntegrityCheckMatch.php';

class SchemaIntegrityCheck
{
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function run()
    {
        if (empty($this->config->getSchemaIntegrity()))
            return;

        $queries = $this->config->getQueries();
        $tables = $this->config->getSchemaIntegrity();
        foreach ($tables as $table => $expectedChecksum)
        {
            $checksum = $queries->getTableSchemaSha1sum($table);
            if ($checksum !== $expectedChecksum)
                yield new SchemaIntegrityCheckMatch($table, $checksum);
        }
        $config = $this->config->getDataintegrity();
        foreach ($queries->getTableNames()->fetchAll(\PDO::FETCH_COLUMN) as $table)
        {
            if (isset($config['ignore']))
            {
                if (preg_match('/'.$config['ignore'].'/', $table))
                    continue;
            }
            if (! isset($tables[$table]))
                yield new SchemaIntegrityCheckMatch($table, 'unexpected table');
        }
    }

    public function generateConfig()
    {
        $queries = $this->config->getQueries();
        $config = $this->config->getDataintegrity();
        foreach ($queries->getTableNames()->fetchAll(\PDO::FETCH_COLUMN) as $table)
        {
            if (isset($config['ignore']))
            {
                if (preg_match('/'.$config['ignore'].'/', $table))
                    continue;
            }
            $checksum = $queries->getTableSchemaSha1sum($table);
            if ($checksum)
                echo "$table = $checksum\n";
        }
    }
}

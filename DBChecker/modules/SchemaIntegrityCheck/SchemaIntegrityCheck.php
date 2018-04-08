<?php

namespace DBChecker\modules\SchemaIntegrityCheck;

use DBChecker\Config;
use DBChecker\DBQueries\AbstractDbQueries;

class SchemaIntegrityCheck
{
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function run(AbstractDbQueries $dbQueries)
    {
        if (empty($this->config->getSchemaIntegrity()))
            return;

        $settings = $this->config->getSchemaIntegrity();
        foreach ($settings['mapping'] as $table => $expectedChecksum)
        {
            $checksum = $dbQueries->getTableSchemaSha1sum($table);
            if ($checksum !== $expectedChecksum)
                yield new SchemaIntegrityCheckMatch($dbQueries->getName(), $table, $checksum);
        }

        if (! $settings['settings']['allow_extras'])
        {
            foreach ($dbQueries->getTableNames()->fetchAll(\PDO::FETCH_COLUMN) as $table)
            {
                foreach ($settings['settings']['ignore'] as $ignore)
                {
                    if (preg_match('/'.$ignore.'/', $table))
                        continue;
                }
                if (! isset($settings['mapping'][$table]))
                    yield new SchemaIntegrityCheckMatch($dbQueries->getName(), $table, 'unexpected table');
            }
        }
    }

    public function generateConfig(AbstractDbQueries $dbQueries)
    {
        $config = $this->config->getDataintegrity();
        foreach ($dbQueries->getTableNames()->fetchAll(\PDO::FETCH_COLUMN) as $table)
        {
            if (isset($config['ignore']))
            {
                if (preg_match('/'.$config['ignore'].'/', $table))
                    continue;
            }
            $checksum = $dbQueries->getTableSchemaSha1sum($table);
            if ($checksum)
                echo "$table = $checksum\n";
        }
    }
}

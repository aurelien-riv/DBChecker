<?php

namespace DBChecker\modules\SchemaIntegrityCheck;

use DBChecker\InputModules\AbstractDBAL;
use DBChecker\ModuleInterface;
use DBChecker\modules\DataIntegrityCheck\DataIntegrityCheckModule;
use DBChecker\ModuleWorkerInterface;

class SchemaIntegrityCheck implements ModuleWorkerInterface
{
    private $config;

    /**
     * SchemaIntegrityCheck constructor.
     * @param ModuleInterface|DataIntegrityCheckModule $module
     */
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
            $checksum = $dbal->getTableSchemaSha1sum($table);
            if ($checksum !== $expectedChecksum)
            {
                yield new SchemaIntegrityCheckMatch($dbal->getName(), $table, $checksum);
            }
        }

        if (! $this->config['allow_extras'])
        {
            yield from $this->checkForExtraTables($dbal);
        }
    }

    private function isIgnored(string $table)
    {
        foreach ($this->config['ignore'] as $ignore)
        {
            if (preg_match('/' . $ignore . '/', $table))
            {
                return true;
            }
        }
        return false;
    }

    public function checkForExtraTables(AbstractDBAL $dbal)
    {
        foreach ($dbal->getTableNames() as $table)
        {
            if (! $this->isIgnored($table) && $this->isExtraTable($table))
            {
                yield new SchemaIntegrityCheckMatch($dbal->getName(), $table, 'unexpected table');
            }
        }
    }

    private function isExtraTable(string $table) : bool
    {
        foreach ($this->config['mapping'] as $mapping)
        {
            if ($table === key($mapping))
            {
                return false;
            }
        }
        return true;
    }

    private function generateIgnores() : string
    {
        $ignores = $this->config['ignore'];
        if (! empty($ignores))
        {
            $ret = "  ignore:\n";
            foreach ($ignores as $ignore)
            {
                $ret .= "    - '$ignore'\n";
            }
            return $ret;
        }
        return '';
    }
    private function generateMapping(DBQueriesInterface $dbQueries) : string
    {
        $ret = "  mapping:\n";
        foreach ($dbQueries->getTableNames() as $table)
        {
            if (! $this->isIgnored($table))
            {
                $checksum = $dbQueries->getTableSchemaSha1sum($table);
                if ($checksum)
                {
                    $ret .= "    - $table: $checksum\n";
                }
            }
        }
        return $ret;
    }

    public function generateConfig(DBQueriesInterface $dbQueries) : string
    {
        $ret = "schemaintegritycheck:\n";
        $ret .= $this->generateIgnores();
        return $ret . $this->generateMapping($dbQueries);
    }
}

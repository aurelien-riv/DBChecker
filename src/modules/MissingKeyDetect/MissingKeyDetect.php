<?php

namespace DBChecker\modules\MissingKeyDetect;

use DBChecker\InputModules\AbstractDBAL;
use DBChecker\ModuleInterface;
use DBChecker\ModuleWorkerInterface;

class MissingKeyDetect implements ModuleWorkerInterface
{
    private $config;

    public function __construct(ModuleInterface $module)
    {
        $this->config = $module->getConfig();
    }

    protected function isPk(AbstractDBAL $dbal, string $table, string $column) : bool
    {
        foreach ($dbal->getPks($table) as $columns)
        {
            if ($columns === $column)
            {
                return true;
            }
        }
        return false;
    }

    protected function isFk(AbstractDBAL $dbal, string $table, string $column) : bool
    {
        return $dbal->getDistantTableAndColumnFromTableAndColumnFK($table, $column) !== null;
    }

    private function isKey(AbstractDBAL $dbal, string $table, string $column) : bool
    {
        return $this->isPk($dbal, $table, $column) || $this->isFk($dbal, $table, $column);
    }

    protected function initAlgorithm(AbstractDBAL $dbal, &$notKeys)
    {
        foreach ($dbal->getTableNames() as $table)
        {
            foreach ($dbal->getColumnNamesInTable($table) as $column)
            {
                if (! $this->isKey($dbal, $table, $column))
                {
                    $notKeys[] = [$table, $column];
                }
            }
        }
    }

    protected function runWithPatterns(string $dbName, $notKeys)
    {
        foreach ($notKeys as $notKey)
        {
            foreach ($this->config['patterns'] as $pattern)
            {
                if (preg_match("/$pattern/", $notKey[1]))
                {
                    yield new MissingKeyDetectMatch($dbName, $notKey[0], $notKey[1]);
                    break;
                }
            }
        }
    }

    public function run(AbstractDBAL $dbal)
    {
        $this->initAlgorithm($dbal, $notKeys);
        yield from $this->runWithPatterns($dbal->getName(), $notKeys);
    }
}
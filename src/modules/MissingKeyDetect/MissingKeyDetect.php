<?php

namespace DBChecker\modules\MissingKeyDetect;

use DBChecker\DBAL\AbstractDBAL;
use DBChecker\DBQueries\AbstractDbQueries;
use DBChecker\ModuleInterface;
use DBChecker\ModuleWorkerInterface;

class MissingKeyDetect implements ModuleWorkerInterface
{
    private $config;

    public function __construct(ModuleInterface $module)
    {
        $this->config = $module->getConfig();
    }

    protected function isPk(AbstractDBAL $dbal, string $table, string $column)
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

    protected function isFk(AbstractDBAL $dbal, string $table, string $column)
    {
        return $dbal->getDistantTableAndColumnFromTableAndColumnFK($table, $column) !== null;
    }

    protected function initAlgorithm(AbstractDBAL $dbal, &$notKeys)
    {
        foreach ($dbal->getTableNames() as $table)
        {
            foreach ($dbal->getColumnNamesInTable($table) as $column)
            {
                if (! $this->isPk($dbal, $table, $column) && ! $this->isFk($dbal, $table, $column))
                {
                    $notKeys[] = [$table, $column];
                }
            }
        }
    }

    /**
     * @param string $identifier
     * @return string[]
     * Splits a snake_case || CamelCase || mixedCamelCase string
     */
    public function split($identifier)
    {
        $matches = [];
        foreach (mb_split('_', $identifier) as $fragment)
        {
            $pattern = '((?:[A-Z]+(?:[a-z]|[0-9])*)|(?:[a-z]+(?:[a-z]|[0-9])*))';
            preg_match_all("/$pattern/", $fragment, $parts);
            array_shift($parts);
            foreach ($parts as $part)
            {
                foreach ($part as $item)
                {
                    $matches[] = $item;
                }
            }
        }
        return $matches;
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
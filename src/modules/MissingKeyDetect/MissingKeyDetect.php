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

    protected function initAlgorithm(AbstractDBAL $dbal, &$notKeys, &$keys)
    {
        foreach ($dbal->getTableNames() as $table)
        {
            foreach ($dbal->getColumnNamesInTable($table) as $column)
            {
                if ($this->isPk($dbal, $table, $column) || $this->isFk($dbal, $table, $column))
                {
                    $keys[] = $column;
                    continue;
                }

                $notKeys[] = [$table, $column];
            }
        }
    }

    public function getIdentifiersFragments($identifiers)
    {
        $fragments = [];
        foreach ($identifiers as $identifier)
        {
            foreach ($this->split($identifier) as $fragment)
            {
                $fragments[] = $fragment;
            }
        }
        return $fragments;
    }
    public function getFrequentIdentifiersFragments($identifiers)
    {
        $fragments = array_count_values($this->getIdentifiersFragments($identifiers));

        $count = count($fragments);
        $threshold = $this->config['threshold'];
        $fragments = array_filter($fragments, function($item) use ($count, $threshold) {
            return ($item > ($count * $threshold / 100));
        });

        return array_keys($fragments);
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

    protected function runWithAutodetect(string $dbName, $notKeys, $keys)
    {
        $keyFragments = $this->getFrequentIdentifiersFragments($keys);
        foreach ($notKeys as $notKey)
        {
            foreach ($this->split($notKey[1]) as $fragment)
            {
                if (in_array($fragment, $keyFragments))
                {
                        yield new MissingKeyDetectMatch($dbName, $notKey[0], $notKey[1]);
                    break;
                }
            }
        }
    }

    public function run(AbstractDBAL $dbal)
    {
        $this->initAlgorithm($dbal, $notKeys, $keys);

        if (! empty($this->config['patterns']))
        {
            yield from $this->runWithPatterns($dbal->getName(), $notKeys);
        }
        else
        {
            yield from $this->runWithAutodetect($dbal->getName(), $notKeys, $keys);
        }
    }
}
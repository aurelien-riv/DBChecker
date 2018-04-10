<?php

namespace DBChecker\modules\MissingKeyDetect;

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

    protected function isPk(DBQueriesInterface $dbQueries, \stdClass $column)
    {
        $pks = $dbQueries->getPks($column->TABLE_NAME)->fetchAll(\PDO::FETCH_OBJ);
        foreach ($pks as $pk)
        {
            if ($pk->Column_name === $column->COLUMN_NAME)
            {
                return true;
            }
        }
        return false;
    }

    protected function isFk(DBQueriesInterface $dbQueries, \stdClass $column)
    {
        return $dbQueries
                   ->getDistantTableAndColumnFromTableAndColumnFK($column->TABLE_NAME, $column->COLUMN_NAME)
                   ->fetch(\PDO::FETCH_OBJ) !== false;
    }

    protected function initAlgorithm(DBQueriesInterface $dbQueries, &$notKeys, &$keys)
    {
        $columns = $dbQueries->getColumnNamesWithTableName()->fetchAll(\PDO::FETCH_OBJ);
        foreach ($columns as $column)
        {
            if ($this->isPk($dbQueries, $column) || $this->isFk($dbQueries,  $column))
            {
                $keys[] = $column->COLUMN_NAME;
                continue;
            }

            $notKeys[] = [$column->TABLE_NAME, $column->COLUMN_NAME];
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
            preg_match_all('/(?:^|[A-Z]|[0-9]+)[a-z]*/', $fragment, $parts);
            foreach ($parts as $part)
            {
                if (isset($part[0]) && ! empty($part[0]))
                {
                    $matches[] = $part[0];
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
                if (preg_match('/' . $pattern . '/', $notKey[1]))
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

    public function run(AbstractDbQueries $dbQueries)
    {
        $this->initAlgorithm($dbQueries, $notKeys, $keys);

        if (! empty($this->config['patterns']))
        {
            yield from $this->runWithPatterns($dbQueries->getName(), $notKeys);
        }
        else
        {
            yield from $this->runWithAutodetect($dbQueries->getName(), $notKeys, $keys);
        }
    }
}
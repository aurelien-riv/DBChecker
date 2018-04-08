<?php

namespace DBChecker\modules\MissingKeyDetect;

use DBChecker\Config;
use DBChecker\DBQueries\AbstractDbQueries;

class MissingKeyDetect
{
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    protected function initAlgorithm(AbstractDbQueries $dbQueries, &$notKeys, &$keys)
    {
        $columnNames = $dbQueries->getColumnNames()->fetchAll(\PDO::FETCH_OBJ);
        foreach ($columnNames as $columnName)
        {
            $pks = $dbQueries->getPks($columnName->TABLE_NAME)->fetchAll(\PDO::FETCH_OBJ);
            foreach ($pks as $pk)
            {
                if ($pk->Column_name === $columnName->COLUMN_NAME)
                {
                    $keys[] = $columnName->COLUMN_NAME;
                    continue;
                }
            }

            $isFk = $dbQueries->getDistantTableAndColumnFromTableAndColumnFK($columnName->TABLE_NAME, $columnName->COLUMN_NAME)
                            ->fetch(\PDO::FETCH_OBJ) !== false;
            if ($isFk)
            {
                $keys[] = $columnName->COLUMN_NAME;
                continue;
            }

            $notKeys[] = [$columnName->TABLE_NAME, $columnName->COLUMN_NAME];
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
        $threshold = $this->config->getMissingKey()['threshold'];
        $fragments = array_filter($fragments, function($item) use ($count, $threshold) {
            return ($item > ($count * $threshold));
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

    public function run(AbstractDbQueries $dbQueries)
    {
        $this->initAlgorithm($dbQueries, $notKeys, $keys);
        $settings = $this->config->getMissingKey();

        if (isset($settings['patterns']))
        {
            foreach ($notKeys as $notKey)
            {
                foreach ($settings['patterns'] as $pattern)
                {
                    if (preg_match('/' . $pattern . '/', $notKey[1]))
                    {
                        yield new MissingKeyDetectMatch($dbQueries->getName(), $notKey[0], $notKey[1]);
                        break;
                    }
                }
            }
        }
        else
        {
            $keyFragments = $this->getFrequentIdentifiersFragments($keys);
            foreach ($notKeys as $notKey)
            {
                foreach ($this->split($notKey[1]) as $fragment)
                {
                    if (in_array($fragment, $keyFragments))
                    {
                        yield new MissingKeyDetectMatch($dbQueries->getName(), $notKey[0], $notKey[1]);
                        break;
                    }
                }
            }
        }
    }
}
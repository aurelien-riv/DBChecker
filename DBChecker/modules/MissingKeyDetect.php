<?php

namespace DBChecker;

require_once 'MissingKeyMatch.php';

class MissingKeyDetect
{
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    protected function initAlgorithm(&$notKeys, &$keys)
    {
        $queries = $this->config->getQueries();
        $columnNames = $queries->getColumnNames()->fetchAll(\PDO::FETCH_OBJ);
        foreach ($columnNames as $columnName)
        {
            $pks = $queries->getPks($columnName->TABLE_NAME)->fetchAll(\PDO::FETCH_OBJ);
            foreach ($pks as $pk)
            {
                if ($pk->Column_name === $columnName->COLUMN_NAME)
                {
                    $keys[] = $columnName->COLUMN_NAME;
                    continue;
                }
            }

            $isFk = $queries->getDistantTableAndColumnFromTableAndColumnFK($columnName->TABLE_NAME, $columnName->COLUMN_NAME)
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
        $fragments = array_filter($fragments, function($item) use ($count) {
            return ($item > ($count * 0.30));
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

    public function run()
    {
        $this->initAlgorithm($notKeys, $keys);

        $keyFragments = $this->getFrequentIdentifiersFragments($keys);

        foreach ($notKeys as $notKey)
        {
            foreach ($this->split($notKey[1]) as $fragment)
            {
                if (in_array($fragment, $keyFragments))
                {
                    yield new MissingKeyMatch($notKey[0], $notKey[1]);
                    break;
                }
            }
        }
    }
}
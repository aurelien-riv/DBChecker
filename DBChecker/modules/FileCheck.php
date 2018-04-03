<?php

namespace DBChecker;

use DBChecker\DBQueries\AbstractDbQueries;

require_once 'FileCheckMatch.php';

class FileCheck
{

    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function run()
    {
        $queries = $this->config->getQueries();
        foreach ($this->config->getFilecheck() as $setting)
        {
            $columns = [];
            $innerJoins = [];
            preg_match_all("/\{(" . AbstractDbQueries::IDENTIFIER ."(?:\." . AbstractDbQueries::IDENTIFIER .")?)\}/", $setting['path'], $matches);
            foreach ($matches[1] as $match)
            {
                $fragments = mb_split('\.', $match);
                if (count($fragments) == 2)
                {
                    $innerJoins[] = $fragments[0];
                }
                $columns[]    = $match;
            }

            $values = $queries->getDistinctValuesWithJoinColumnsWithoutNulls($setting['table'], $columns, $innerJoins)
                              ->fetchAll(\PDO::FETCH_OBJ);
            foreach ($values as $value)
            {
                $path = preg_replace_callback("/\{(" . AbstractDbQueries::IDENTIFIER ."(?:\." . AbstractDbQueries::IDENTIFIER .")?)\}/", function($match) use ($value, $setting) {
                    return $value->{$match[1]};
                }, $setting['path']);

                if (! is_file($path))
                {
                    yield new FileCheckMatch($setting['table'], $columns, $path);
                }
            }
        }
    }
}
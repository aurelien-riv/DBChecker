<?php

namespace DBChecker;

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
            if (! is_dir($setting['path']))
            {
                yield new FileCheckMatch($setting['table'], $setting['column'], $setting['path'], "");
            }
            else
            {
                $values = $queries->getDistinctValuesWithoutNulls($setting->table, $setting->column)->fetchAll(\PDO::FETCH_COLUMN);
                foreach ($values as $value)
                {
                    if (! is_file($setting['path'].'/'.$value))
                        yield new FileCheckMatch($setting['table'], $setting['column'], $setting['path'], $value);
                }
            }
        }
    }
}
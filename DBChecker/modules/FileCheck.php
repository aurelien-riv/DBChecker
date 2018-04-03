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
            $columns = [];
            preg_match_all('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', $setting['path'], $matches);
            if (count($matches) === 2)
            {
                foreach ($matches[1] as $match)
                {
                    $columns[] = $match;
                }
            }

            $values = $queries->getDistinctValuesWithoutNulls($setting['table'], $columns)
                              ->fetchAll(\PDO::FETCH_OBJ);
            foreach ($values as $value)
            {
                $path = preg_replace_callback('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', function($match) use ($value) {
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
<?php

namespace DBChecker;

require_once 'UniqueIntegrityMatch.php';

class UniqueIntegrity
{
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function run()
    {
        $queries = $this->config->getQueries();

        $tables = $queries->getTableNames()->fetchAll(\PDO::FETCH_COLUMN);
        foreach ($tables as $table)
        {
            $indexColumns = $queries->getUniqueIndexes($table)->fetchAll(\PDO::FETCH_COLUMN);
            foreach ($indexColumns as $columns)
            {
                $resultset = $queries->getDuplicateForColumnsWithCount($table, $columns)->fetchAll(\PDO::FETCH_COLUMN);
                foreach ($resultset as $result)
                {
                    yield new UniqueIntegrityMatch($table, $columns, $result);
                }
            }
        }
    }
}
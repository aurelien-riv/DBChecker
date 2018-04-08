<?php

namespace DBChecker\modules\UniqueIntegrityCheck;

use DBChecker\Config;

class UniqueIntegrityCheck
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
                $resultset = $queries->getDuplicateForColumnsWithCount($table, $columns)->fetchAll(\PDO::FETCH_OBJ);
                foreach ($resultset as $result)
                {
                    yield new UniqueIntegrityCheckMatch($table, $columns, $result);
                }
            }
        }
    }
}
<?php

namespace DBChecker;

require_once('Config.php');

class DBChecker
{
    private $config;

    public function __construct()
    {
        $this->config = new Config();
    }

    public function run()
    {
        $queries = $this->config->getQueries();

        $tables = $queries->getTableNames()->fetchAll(\PDO::FETCH_COLUMN);
        foreach ($tables as $table)
        {
            $fkeys = $queries->getFk($table)->fetchAll(\PDO::FETCH_OBJ);
            foreach ($fkeys as $fkey)
            {
                $values = $queries->getDistinctValuesWithoutNulls($table, $fkey->REFERENCED_COLUMN_NAME)->fetchAll(\PDO::FETCH_COLUMN);
                foreach ($values as $value)
                {
                    $valueExists = $queries->getValue($fkey->REFERENCED_TABLE_NAME, $fkey->REFERENCED_COLUMN_NAME, $value)->fetchAll();
                    if (empty($valueExists))
                    {
                        yield "$table.{$fkey->COLUMN_NAME} -> {$fkey->REFERENCED_TABLE_NAME}.{$fkey->REFERENCED_COLUMN_NAME} # $value\n";
                    }
                }
            }
        }
    }
}
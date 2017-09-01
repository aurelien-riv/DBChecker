<?php

namespace DBChecker;

require_once 'RelCheckMatch.php';

class RelCheck
{
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function run()
    {
        $queries = $this->config->getQueries();

        $fkeys = $queries->getFks()->fetchAll(\PDO::FETCH_OBJ);
        foreach ($fkeys as $fkey)
        {
            $values = $queries->getDistinctValuesWithoutNulls($fkey->TABLE_NAME, $fkey->REFERENCED_COLUMN_NAME)->fetchAll(\PDO::FETCH_COLUMN);
            foreach ($values as $value)
            {
                $valueExists = $queries->getValue($fkey->REFERENCED_TABLE_NAME, $fkey->REFERENCED_COLUMN_NAME, $value)->fetchAll();
                if (empty($valueExists))
                {
                    yield new RelCheckMatch($fkey->TABLE_NAME, $fkey->COLUMN_NAME, $fkey->REFERENCED_TABLE_NAME, $fkey->REFERENCED_COLUMN_NAME, $value);
                }
            }
        }
    }
}
<?php

namespace DBChecker;

require_once 'RelCheckMatch.php';
require_once 'TableNotFoundMatch.php';
require_once 'ColumnNotFoundMatch.php';

class RelCheck
{
    private $config;
    private $tables;

    public function __construct(Config $config)
    {
        $this->config = $config;

        $this->tables = $this->config->getQueries()->getTableNames()->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function run()
    {
        $queries = $this->config->getQueries();

        $fkeys = $queries->getFks()->fetchAll(\PDO::FETCH_OBJ);
        foreach ($fkeys as $fkey)
        {
            $schemaError = false;

            $tbl  = $fkey->TABLE_NAME;
            $rtbl = $fkey->REFERENCED_TABLE_NAME;
            $col  = $fkey->COLUMN_NAME;
            $rcol = $fkey->REFERENCED_COLUMN_NAME;

            foreach ($this->checkSchema($tbl, $col) as $error)
            {
                $schemaError = true;
                yield $error;
            }
            foreach ($this->checkSchema($rtbl, $rcol) as $error)
            {
                $schemaError = true;
                yield $error;
            }

            if ($schemaError)
                continue;

            $values = $queries->getDistinctValuesWithoutNulls($tbl, $col)->fetchAll(\PDO::FETCH_COLUMN);
            foreach ($values as $value)
            {
                $valueExists = $queries->getValue($rtbl, $rcol, $value)->fetchAll();
                if (empty($valueExists))
                {
                    yield new RelCheckMatch($tbl, $col, $rtbl, $rcol, $value);
                }
            }
        }
    }

    /**
     * Checks whether the table $tbl exists and contains the column $col
     * @param string $tbl The name of the column that is supposed to exist in the database
     * @param string $col The name of the column that is supposed to exist in the table $tbl
     * @return \Generator
     */
    public function checkSchema($tbl, $col)
    {
        if (! in_array($tbl, $this->tables))
        {
            yield new TableNotFoundMatch($tbl);
        }
        else
        {
            $columns = $this->config->getQueries()->getColumnNamesInTable($tbl)->fetchAll(\PDO::FETCH_COLUMN);
            if (! in_array($col, $columns))
                yield new ColumnNotFoundMatch($tbl, $col);
        }
    }
}
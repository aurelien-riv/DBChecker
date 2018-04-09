<?php

namespace DBChecker\modules\RelCheck;

use DBChecker\DBQueries\AbstractDbQueries;

class RelCheck
{
    private $tables;

    public function run(AbstractDbQueries $dbQueries)
    {
        $this->tables = $dbQueries->getTableNames()->fetchAll(\PDO::FETCH_COLUMN);

        $fkeys = $dbQueries->getFks()->fetchAll(\PDO::FETCH_OBJ);
        foreach ($fkeys as $fkey)
        {
            $schemaError = false;

            $tbl  = $fkey->TABLE_NAME;
            $rtbl = $fkey->REFERENCED_TABLE_NAME;
            $col  = $fkey->COLUMN_NAME;
            $rcol = $fkey->REFERENCED_COLUMN_NAME;

            foreach ($this->checkSchema($dbQueries, $tbl, $col) as $error)
            {
                $schemaError = true;
                yield $error;
            }
            foreach ($this->checkSchema($dbQueries, $rtbl, $rcol) as $error)
            {
                $schemaError = true;
                yield $error;
            }

            if ($schemaError)
                continue;

            $values = $dbQueries->getDistinctValuesWithoutNulls($tbl, $col)->fetchAll(\PDO::FETCH_COLUMN);
            foreach ($values as $value)
            {
                $valueExists = $dbQueries->getValue($rtbl, $rcol, $value)->fetchAll();
                if (empty($valueExists))
                {
                    yield new RelCheckMatch($dbQueries->getName(), $tbl, $col, $rtbl, $rcol, $value);
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
    public function checkSchema(AbstractDbQueries $dbQueries, $tbl, $col)
    {
        if (! in_array($tbl, $this->tables))
        {
            yield new TableNotFoundMatch($dbQueries->getName(), $tbl);
        }
        else
        {
            $columns = $dbQueries->getColumnNamesInTable($tbl)->fetchAll(\PDO::FETCH_COLUMN);
            if (! in_array($col, $columns))
                yield new ColumnNotFoundMatch($dbQueries->getName(), $tbl, $col);
        }
    }
}
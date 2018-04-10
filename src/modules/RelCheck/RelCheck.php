<?php

namespace DBChecker\modules\RelCheck;

use DBChecker\DBQueries\AbstractDbQueries;
use DBChecker\ModuleWorkerInterface;

class RelCheck implements ModuleWorkerInterface
{
    private $tables;

    public function run(AbstractDbQueries $dbQueries)
    {
        $this->tables = $dbQueries->getTableNames()->fetchAll(\PDO::FETCH_COLUMN);

        $fkeys = $dbQueries->getFks()->fetchAll(\PDO::FETCH_OBJ);
        foreach ($fkeys as $fkey)
        {
            $tbl         = $fkey->TABLE_NAME;
            $rtbl        = $fkey->REFERENCED_TABLE_NAME;
            $col         = $fkey->COLUMN_NAME;
            $rcol        = $fkey->REFERENCED_COLUMN_NAME;

            $errors = $this->checkSourceAndReferencedColumns($dbQueries, $tbl, $col, $rtbl, $rcol);
            if ($errors->current())
            {
                yield from $errors;
                continue;
            }

            yield from $this->checkRelations($dbQueries, $tbl, $col, $rtbl, $rcol);
        }
    }

    protected function checkRelations(DBQueriesInterface $dbQueries, $tbl, $col, $rtbl, $rcol)
    {
        $values = $dbQueries->getDistinctValuesWithoutNulls($tbl, $col)->fetchAll(\PDO::FETCH_COLUMN);
        foreach ($values as $value)
        {
            $valueExists = $dbQueries->getValue($rtbl, $rcol, $value)->fetch();
            if (empty($valueExists))
            {
                yield new RelCheckMatch($dbQueries->getName(), $tbl, $col, $rtbl, $rcol, $value);
            }
        }
    }

    public function checkSourceAndReferencedColumns(DBQueriesInterface $dbQueries, $tbl, $col, $rtbl, $rcol)
    {
        yield from $this->checkSchema($dbQueries, $tbl,  $col);
        yield from $this->checkSchema($dbQueries, $rtbl, $rcol);
    }

    /**
     * Checks whether the table $tbl exists and contains the column $col
     * @param DBQueriesInterface $dbQueries
     * @param string            $tbl The name of the column that is supposed to exist in the database
     * @param string            $col The name of the column that is supposed to exist in the table $tbl
     * @return \Generator
     */
    public function checkSchema(DBQueriesInterface $dbQueries, $tbl, $col)
    {
        if (! in_array($tbl, $this->tables))
        {
            yield new TableNotFoundMatch($dbQueries->getName(), $tbl);
            return;
        }

        $columns = $dbQueries->getColumnNamesInTable($tbl)->fetchAll(\PDO::FETCH_COLUMN);
        if (! in_array($col, $columns))
        {
            yield new ColumnNotFoundMatch($dbQueries->getName(), $tbl, $col);
        }
    }
}
<?php

namespace DBChecker\modules\RelCheck;

use DBChecker\InputModules\AbstractDBAL;
use DBChecker\ModuleWorkerInterface;

class RelCheck implements ModuleWorkerInterface
{
    private $tables;

    private function init(AbstractDBAL $dbal)
    {
        $this->tables = $dbal->getTableNames();
    }

    public function run(AbstractDBAL $dbal)
    {
        $this->init($dbal);

        foreach ($dbal->getFks() as $fkey)
        {
            $tbl  = $fkey['TABLE_NAME'];
            $rtbl = $fkey['REFERENCED_TABLE_NAME'];
            $col  = $fkey['COLUMN_NAME'];
            $rcol = $fkey['REFERENCED_COLUMN_NAME'];

            yield from $this->checkSourceAndReferencedColumns($dbal, $tbl, $col, $rtbl, $rcol);
            yield from $this->checkRelations($dbal, $tbl, $col, $rtbl, $rcol);
        }
    }

    protected function checkRelations(AbstractDBAL $dbal, string $tbl, string $col, string $rtbl, string $rcol)
    {
        $values = $dbal->getDistinctValuesWithoutNulls($tbl, [$col]);
        foreach ($values as $value)
        {
            $valueExists = $dbal->getValue($rtbl, $rcol, $value);
            if (empty($valueExists))
            {
                yield new RelCheckMatch($dbal->getName(), $tbl, $col, $rtbl, $rcol, $value);
            }
        }
    }

    public function checkSourceAndReferencedColumns(AbstractDBAL $dbal, $tbl, $col, $rtbl, $rcol)
    {
        yield from $this->checkSchema($dbal, $tbl,  $col);
        yield from $this->checkSchema($dbal, $rtbl, $rcol);
    }

    /**
     * Checks whether the table $tbl exists and contains the column $col
     * @param AbstractDBAL $dbal
     * @param string       $tbl The name of the column that is supposed to exist in the database
     * @param string       $col The name of the column that is supposed to exist in the table $tbl
     * @return \Generator
     */
    public function checkSchema(AbstractDBAL $dbal, $tbl, $col)
    {
        if (! in_array($tbl, $this->tables))
        {
            yield new TableNotFoundMatch($dbal->getName(), $tbl);
            return;
        }

        $columns = $dbal->getColumnNamesInTable($tbl);
        if (! in_array($col, $columns))
        {
            yield new ColumnNotFoundMatch($dbal->getName(), $tbl, $col);
        }
    }
}
<?php

namespace DBChecker\DBAL;

use DBChecker\DBQueries\SQLiteQueries;

/**
 * @property SQLiteQueries $queries
 */
class SQLiteDBAL extends AbstractDBAL
{
    public function getTableNames() : array
    {
        return $this->queries->getTableNames()->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function getFks() : array
    {
        $data = [];
        foreach ($this->getTableNames() as $table)
        {
            $datum = $this->queries->getFksForTable($table)->fetch(\PDO::FETCH_ASSOC);
            if (! empty($datum))
            {
                $data[] = [
                    'TABLE_NAME'             => $table,
                    'REFERENCED_TABLE_NAME'  => $datum['table'],
                    'COLUMN_NAME'            => $datum['from'],
                    'REFERENCED_COLUMN_NAME' => $datum['to']
                ];
            }
        }
        return $data;
    }

    public function getColumnNamesInTable(string $table) : array
    {
        $data = [];
        $columns = $this->queries->getColumnNamesInTable($table)->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($columns as $column)
        {
            $data[] = $column['name'];
        }
        return $data;
    }

    public function getTableDataSha1sum(string $table) : string
    {
        return $this->queries->getTableDataSha1sum($table);
    }

    public function getTableSchemaSha1sum(string $table) : string
    {
        return $this->queries->getTableSchemaSha1sum($table);
    }
}
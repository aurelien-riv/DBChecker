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

    public function getDistantTableAndColumnFromTableAndColumnFK(string $table, string $column) : ?array
    {
        $data = $this->queries->getFksForTable($table)->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($data ? $data : [] as $datum)
        {
            if ($datum['from'] === $column)
            {
                return [
                    'table'  => $datum['table'],
                    'column' => $datum['to']
                ];
            }
        }
        return null;
    }

    public function getFks() : array
    {
        $data = [];
        foreach ($this->getTableNames() as $table)
        {
            foreach ($this->queries->getFksForTable($table)->fetchAll(\PDO::FETCH_ASSOC) as $datum)
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

    public function getUniqueIndexes(string $table) : array
    {
        $data = [];
        $indexes = $this->queries->getIndexList($table)->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($indexes as $index)
        {
            if ($index['unique'] === '1')
            {
                $columnNames = '';
                $columns = $this->queries->getIndexInfo($index['name'])->fetchAll(\PDO::FETCH_ASSOC);
                foreach ($columns as $column)
                {
                    $columnNames .= $column['name'].',';
                }
                $data[] = rtrim($columnNames, ',');
            }
        }
        return $data;
    }

    public function getColumnNamesInTable(string $table) : array
    {
        $data = [];
        $columns = $this->queries->getTableInfo($table)->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($columns as $column)
        {
            $data[] = $column['name'];
        }
        return $data;
    }

    public function getPKs(string $table) : array
    {
        $data = [];
        $columns = $this->queries->getTableInfo($table)->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($columns as $column)
        {
            if ($column['pk'] == "1")
            {
                $data[] = $column['name'];
            }
        }
        return $data;
    }

    public function getTableDataSha1sum(string $table) : string
    {
        $concatenated = '';
        $data = $this->queries->selectEverythingFrom($table)->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($data as $datum)
        {
            foreach ($datum as $item)
            {
                $concatenated .= $item;
            }
        }
        return hash('sha1', $concatenated);
    }

    public function getTableSchemaSha1sum(string $table) : string
    {
        return hash('sha1', implode(',', $this->getColumnNamesInTable($table)));
    }
}
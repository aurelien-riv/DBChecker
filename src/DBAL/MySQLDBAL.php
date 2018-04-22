<?php

namespace DBChecker\DBAL;

use DBChecker\DBQueries\MySQLQueries;

/**
 * @property MySQLQueries $queries
 */
class MySQLDBAL extends AbstractDBAL
{
    public function getTableNames() : array
    {
        return $this->queries->getTableNames()->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function getColumnNamesInTable(string $table) : array
    {
        return $this->queries->getColumnNamesInTable($table)->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function getFks() : array
    {
        return $this->queries->getFks()->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getPKs(string $table) : array
    {
        $array = [];
        $data = $this->queries->getPKs($table)->fetchAll(\PDO::FETCH_OBJ);
        foreach ($data as $datum)
        {
            $array[] = $datum->Column_name;
        }
        return $array;
    }

    public function getTableDataSha1sum(string $table) : string
    {
        return $this->queries->getTableDataSha1sum($table);
    }

    public function getTableSchemaSha1sum(string $table) : string
    {
        return $this->queries->getTableSchemaSha1sum($table);
    }

    public function getFragmentedTables() : array
    {
        return $this->queries->getFragmentedTables()->fetchAll(\PDO::FETCH_OBJ);
    }

    public function getTableLargerThanMb(int $minSize_MB) : array
    {
        return $this->queries->getTableLargerThanMb($minSize_MB)->fetchAll(\PDO::FETCH_OBJ);
    }

    public function getRandomValuesConcatenated(string $table, int $limit) : string
    {
        return $this->queries->getRandomValuesConcatenated($table, $limit)->fetch(\PDO::FETCH_COLUMN);
    }

    public function getDistantTableAndColumnFromTableAndColumnFK(string $table, string $column) : ?array
    {
        $data = $this->queries->getDistantTableAndColumnFromTableAndColumnFK($table, $column)->fetch(\PDO::FETCH_OBJ);
        if ($data !== false)
        {
            return [
                'table'  => $data->REFERENCED_TABLE_NAME,
                'column' => $data->REFERENCED_COLUMN_NAME
            ];
        }
        return null;
    }

    public function getUniqueIndexes(string $table) : array
    {
        return $this->queries->getUniqueIndexes($table)->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function analyzeTable(string $table) : \stdClass
    {
        return $this->queries->analyzeTable($table)->fetch(\PDO::FETCH_OBJ);
    }


    public function supportsTablespaceCompression() : bool
    {
        return true;
    }

    public function isTableCompressed(string $table) : bool
    {
        return $this->queries->isTableCompressed($table);
    }
}
<?php

namespace DBChecker\DBQueries;

use DBChecker\DBAL\AbstractDBAL;
use DBChecker\DBAL\SQLiteDBAL;

class SQLiteQueries extends AbstractDbQueries
{
    public function getTableNames() : \PDOStatement
    {
        return $this->pdo->query("SELECT name FROM sqlite_master WHERE type='table';");
    }

    public function getFksForTable($table) : \PDOStatement
    {
        return $this->pdo->query("PRAGMA foreign_key_list($table);");
    }

    public function getColumnNamesInTable($table)
    {
        return $this->pdo->query("PRAGMA table_info($table);");
    }

    public function getDistantTableAndColumnFromTableAndColumnFK(string $table, string $column) : \PDOStatement
    {
        throw new \BadMethodCallException(static::NOT_IMPLEMENTED_ERROR_MSG);
    }
}
<?php

namespace DBChecker\DBQueries;

class MySQLQueries
{
    private $pdo;
    private $database = null;

    public function __construct(\PDO $pdo, string $database)
    {
        $this->database = $database;
        $this->pdo = $pdo;
    }

    public function getTableNames() : \PDOStatement
    {
        return $this->pdo->query("SHOW TABLES;");
    }

    public function getFk(string $table) : \PDOStatement
    {
        $stmt = $this->pdo->prepare("
            SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE
              REFERENCED_TABLE_SCHEMA = :database AND TABLE_NAME = :table;
        ");
        $stmt->bindParam(':database', $this->database, \PDO::PARAM_STR);
        $stmt->bindParam(':table',    $table,          \PDO::PARAM_STR);
        $stmt->execute();
        return $stmt;
    }

    public function getDistinctValuesWithoutNulls($table, $column) : \PDOStatement
    {
        $stmt = $this->pdo->prepare("SELECT DISTINCT $column FROM $table WHERE $column IS NOT NULL;");
        $stmt->execute();
        return $stmt;
    }

    public function getValue($table, $column, $value) : \PDOStatement
    {
        $stmt = $this->pdo->prepare("SELECT DISTINCT $column FROM $table WHERE $column = :value;");
        $stmt->bindParam(':value', $value, \PDO::PARAM_STR);
        $stmt->execute();
        return $stmt;
    }
}
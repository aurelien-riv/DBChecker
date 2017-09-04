<?php

namespace DBChecker\DBQueries;

require_once 'AbstractDbQueries.php';

class MySQLQueries extends AbstractDbQueries
{
    public function getTableNames()
    {
        return $this->pdo->query("SHOW TABLES;");
    }

    public function getFks()
    {
        $stmt = $this->pdo->prepare("
            SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE REFERENCED_TABLE_SCHEMA = DATABASE();
        ");
        $stmt->execute();
        return $stmt;
    }

    public function getDistinctValuesWithoutNulls($table, $column)
    {
        $stmt = $this->pdo->prepare("SELECT DISTINCT $column FROM $table WHERE $column IS NOT NULL;");
        $stmt->execute();
        return $stmt;
    }

    public function getValue($table, $column, $value)
    {
        $stmt = $this->pdo->prepare("SELECT DISTINCT $column FROM $table WHERE $column = :value;");
        $stmt->bindParam(':value', $value, \PDO::PARAM_STR);
        $stmt->execute();
        return $stmt;
    }

    public function getConcatenatedColumnNames($table)
    {
        $stmt = $this->pdo->prepare("
            SELECT GROUP_CONCAT(column_name)
            FROM information_schema.columns 
            WHERE table_schema=DATABASE() AND table_name=:table
        ");
        $stmt->bindParam(':table', $table, \PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetchColumn();
        $stmt->closeCursor();
        return $result;

    }
    public function getTableSha1sum($table)
    {
        $columns = $this->getConcatenatedColumnNames($table);
        $stmt = $this->pdo->prepare("select SHA1(group_concat(:columns)) from $table");
        $stmt->bindParam(':columns', $columns, \PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetchColumn();
        $stmt->closeCursor();
        if (empty($result))
            $result = 0;
        return $result;
    }
}
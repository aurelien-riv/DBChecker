<?php

namespace DBChecker\DBQueries;

class MySQLQueries
{
    private $pdo;
    private $database = null;

    public function __construct(\PDO $pdo, $database)
    {
        $this->database = $database;
        $this->pdo = $pdo;
    }

    #region relcheck
    public function getFks()
    {
        $stmt = $this->pdo->prepare("
            SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE REFERENCED_TABLE_SCHEMA = :database;
        ");
        $stmt->bindParam(':database', $this->database, \PDO::PARAM_STR);
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
    #endregion

    #region filecheck
    public function createFilecheckTable()
    {
        $query = "CREATE TABLE `_sqldb_checker_filecheck` (
                `table` varchar(64) NOT NULL,
                `column` varchar(64) NOT NULL,
                `basepath` varchar(512) NOT NULL
            ) ENGINE='MyISAM' COLLATE 'utf8_general_ci';";
        $this->pdo->exec($query);
    }

    public function getFilecheckSettings()
    {
        $stmt = $this->pdo->prepare("SELECT `table`, `column`, `basepath` FROM _sqldb_checker_filecheck;");
        $stmt->execute();
        return $stmt;
    }
    #endregion
}
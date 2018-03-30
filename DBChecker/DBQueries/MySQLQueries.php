<?php

namespace DBChecker\DBQueries;

require_once 'AbstractDbQueries.php';

class MySQLQueries extends AbstractDbQueries
{
    public function getTableNames()
    {
        return $this->pdo->query("SHOW TABLES;");
    }

    public function getColumnNames($table)
    {
        return $this->pdo->query("SHOW COLUMNS FROM $table");
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

    protected function getUniqueIndexesV1($table)
    {
        $stmt = $this->pdo->prepare("
            SELECT GROUP_CONCAT(f.name)
            FROM information_schema.innodb_sys_tables  t 
            JOIN information_schema.innodb_sys_indexes i USING (table_id) 
            JOIN information_schema.innodb_sys_fields  f USING (index_id)
            WHERE 
                t.schema = DATABASE() 
                AND t.name = '$table'
                AND i.TYPE = 2
            GROUP BY f.index_id
        ");
        $stmt->execute();
        return $stmt;
    }
    protected function getUniqueIndexesV2($table)
    {
        $stmt = $this->pdo->prepare("
            SELECT GROUP_CONCAT(f.name)
            FROM information_schema.innodb_sys_tables  t 
            JOIN information_schema.innodb_sys_indexes i USING (table_id) 
            JOIN information_schema.innodb_sys_fields  f USING (index_id)
            WHERE 
                t.name = CONCAT(DATABASE(), '/$table')
                AND i.TYPE = 2
            GROUP BY f.index_id
        ");
        $stmt->execute();
        return $stmt;
    }
    public function getUniqueIndexes($table)
    {
        $stmt = $this->getUniqueIndexesV1($table);
        $error = $stmt->errorInfo();
        if (isset($error[1]) && $error[1] === 1054)
        {
            $stmt = $this->getUniqueIndexesV2($table);
        }
        return $stmt;
    }

    public function getDuplicateForColumnsWithCount($table, $columns)
    {
        $stmt = $this->pdo->prepare("
            SELECT $columns, COUNT(*)
            FROM tbl_user
            GROUP BY $columns
            HAVING COUNT(*) > 1 AND CONCAT_WS($columns, NULL) IS NOT NULL
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

    public function getTableDataSha1sum($table)
    {
        $columns = $this->getConcatenatedColumnNames($table);
        $stmt = $this->pdo->prepare("SELECT SHA1(group_concat(:columns)) FROM $table");
        $stmt->bindParam(':columns', $columns, \PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetchColumn();
        $stmt->closeCursor();
        return empty($result) ? 0 : $result;
    }

    public function getTableSchemaSha1sum($table)
    {
        $columns = $this->getConcatenatedColumnNames($table);
        return hash('sha1', $columns);
    }
}

<?php

namespace DBChecker\DBQueries;

use DBChecker\DBAL\AbstractDBAL;
use DBChecker\DBAL\MySQLDBAL;

class MySQLQueries extends AbstractDbQueries
{
    public function getTableNames() : \PDOStatement
    {
        return $this->pdo->query("SHOW FULL TABLES WHERE Table_Type = 'BASE TABLE'");
    }

    public function getColumnNamesInTable($table)
    {
        return $this->pdo->query("SHOW COLUMNS FROM $table");
    }

    public function getPKs($table)
    {
        return $this->pdo->query("SHOW INDEX FROM $table WHERE Key_name = 'PRIMARY'");
    }

    public function getDistantTableAndColumnFromTableAndColumnFK(string $table, string $column)
    {
        $stmt = $this->pdo->prepare("
            SELECT REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE 
                REFERENCED_TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME  = :table
                AND COLUMN_NAME = :column;
        ");
        $stmt->bindParam(':table',  $table,  \PDO::PARAM_STR);
        $stmt->bindParam(':column', $column, \PDO::PARAM_STR);
        $stmt->execute();
        return $stmt;
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

    #region UniqueIndex
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
        try
        {
            return $this->getUniqueIndexesV1($table);
        }
        catch (\PDOException $e)
        {
            return $this->getUniqueIndexesV2($table);
        }
    }
    #endregion

    protected function getConcatenatedColumnNames($table)
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
        $stmt = $this->pdo->prepare("SELECT SHA1(CONCAT_WS(:columns)) FROM $table");
        $stmt->bindParam(':columns', $columns, \PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetchColumn();
        $stmt->closeCursor();
        return empty($result) ? 0 : $result;
    }

    // FIXME should not use only the column names!
    public function getTableSchemaSha1sum($table) : string
    {
        $columns = $this->getConcatenatedColumnNames($table);
        return hash('sha1', $columns);
    }

    #region Compression
    public function supportsTablespaceCompression() : bool
    {
        return true; // FIXME depends on the version of MySQL/MariaDB
    }

    public function isTableCompressed(string $table) : bool
    {
        $stmt = $this->pdo->prepare("
            SELECT ROW_FORMAT 
            FROM information_schema.TABLES 
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table;
        ");
        $stmt->bindParam(':table', $table, \PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_OBJ);

        return (in_array($result->ROW_FORMAT, ['Compressed', 'tokudb_zlib']));
    }

    public function getRandomValuesConcatenated(string $table, int $limit) : \PDOStatement
    {
        $columns = $this->getConcatenatedColumnNames($table);
        $stmt = $this->pdo->prepare("
            SELECT CONCAT_WS($columns) 
            FROM (
                SELECT *, 1 AS grp 
                FROM $table 
                ORDER BY RAND() 
                LIMIT :limit
            ) AS d 
            GROUP BY d.grp;
        ");
        $stmt->bindParam(':limit',   $limit,   \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    public function getTableLargerThanMb(int $minSize_MB) : \PDOStatement
    {
        $stmt = $this->pdo->prepare("
            SELECT TABLE_NAME 
            FROM information_schema.TABLES 
            WHERE table_schema = DATABASE() AND ((data_length + index_length) / 1024 / 1024) > :minSize;
        ");
        $stmt->bindParam(':minSize', $minSize_MB, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }
    #endregion

    public function getFragmentedTables() : \PDOStatement
    {
        return $this->pdo->query("
          SELECT `TABLE_NAME`, DATA_FREE*100/(DATA_LENGTH+INDEX_LENGTH+DATA_FREE) AS FRAGMENTATION
          FROM information_schema.TABLES 
          WHERE TABLE_SCHEMA = DATABASE() 
            AND DATA_LENGTH/1024/1024 > 10 
            AND NOT ENGINE='MEMORY'
          HAVING FRAGMENTATION > 10 ");
    }

    public function analyzeTable(string $table) : \PDOStatement
    {
        return $this->pdo->query("ANALYZE TABLE $table");
    }
}

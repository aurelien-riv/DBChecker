<?php

namespace DBChecker\DBQueries;

class MySQLQueries extends AbstractDbQueries
{
    public function getTableNames()
    {
        return $this->pdo->query("SHOW TABLES;");
    }

    public function getColumnNames()
    {
        $stmt = $this->pdo->prepare("
            SELECT TABLE_NAME, COLUMN_NAME
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE();
        ");
        $stmt->execute();
        return $stmt;
    }

    public function getColumnNamesInTable($table)
    {
        return $this->pdo->query("SHOW COLUMNS FROM $table");
    }

    public function getPKs($table)
    {
        return $this->pdo->query("SHOW INDEX FROM $table WHERE Key_name = 'PRIMARY'");
    }

    public function getDistantTableAndColumnFromTableAndColumnFK($table, $column)
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
        $query = "
            SELECT $columns, COUNT(*) as __count__
            FROM $table
            GROUP BY $columns
            HAVING COUNT(*) > 1
        ";
        // If there is one column, ignore null values
        if (! strpos($columns, ','))
        {
            $query .= " AND $columns IS NOT NULL";
        }
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getDistinctValuesWithoutNulls($table, $columns)
    {
        $selectColumns = $whereColumns = $columns;
        if (is_array($columns))
        {
            $selectColumns = implode(',', $columns);
            $whereColumns = implode(' IS NOT NULL AND ', $columns);
        }
        $query = "SELECT DISTINCT $selectColumns FROM $table WHERE $whereColumns IS NOT NULL;";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    /**
     * @param string   $table
     * @param string[] $columns
     * @param string[] $innerJoinColumns
     * @return bool|\PDOStatement
     * Warning, composed key are not supported yet
     */
    public function getDistinctValuesWithJoinColumnsWithoutNulls($table, $columns, $innerJoinColumns)
    {
        $columns          = array_unique($columns);
        $innerJoinColumns = array_unique($innerJoinColumns);

        $joins = '';
        foreach ($innerJoinColumns as $innerJoinColumn)
        {
            $relation = $this->getDistantTableAndColumnFromTableAndColumnFK($table, $innerJoinColumn)
                             ->fetch(\PDO::FETCH_OBJ);

            $joins .= "INNER JOIN {$relation->REFERENCED_TABLE_NAME} AS $innerJoinColumn
                ON $innerJoinColumn.{$relation->REFERENCED_COLUMN_NAME} = $table.$innerJoinColumn ";
        }

        $selectColumns = '';
        foreach ($columns as $column)
        {
            $selectColumns .= "$column as `$column`,";
        }
        $stmt = $this->pdo->prepare("SELECT DISTINCT " . rtrim($selectColumns, ',')
                                    . " FROM $table $joins"
                                    . " WHERE " . implode(' IS NOT NULL AND ', $columns) . " IS NOT NULL;");
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

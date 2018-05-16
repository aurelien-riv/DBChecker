<?php

namespace DBChecker\InputModules;

abstract class  AbstractDbQueries
{
    /**
     * Regex that matches a valid column name
     */
    const IDENTIFIER = '[a-zA-Z_][a-zA-Z0-9_]*';

    protected $pdo;
    protected $name;

    const NOT_IMPLEMENTED_ERROR_MSG = "This method is not available for this database";
    
    public function __construct(\PDO $pdo, $name)
    {
        $this->pdo = $pdo;
        $this->name = $name;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getValue($table, $column, $value)
    {
        $stmt = $this->pdo->prepare("SELECT DISTINCT $column FROM $table WHERE $column = :value LIMIT 1;");
        $stmt->bindParam(':value', $value, \PDO::PARAM_STR);
        $stmt->execute();
        return $stmt;
    }

    public function getDuplicateForColumnsWithCount(string $table, string $columns)
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
        return $this->pdo->query($query);
    }

    public function getDistinctValuesWithoutNulls($table, $columns)
    {
        $selectColumns = $whereColumns = $columns;
        if (is_array($columns))
        {
            $selectColumns = implode(',', $columns);
            $whereColumns = implode(' IS NOT NULL AND ', $columns);
        }
        return $this->pdo->query("SELECT DISTINCT $selectColumns FROM $table WHERE $whereColumns IS NOT NULL;");
    }

    public abstract function getDistantTableAndColumnFromTableAndColumnFK(string $table, string $column) : \PDOStatement;

    /**
     * @param string   $table
     * @param string[] $columns
     * @param          $innerJoinString
     * @param int      $limit
     * @param int      $offset
     * @return bool|\PDOStatement
     * Warning, composed key are not supported yet
     */
    public function getDistinctValuesWithJoinColumnsWithoutNulls($table, $columns, $innerJoinString, int $limit, int $offset) : \PDOStatement
    {
        $columns = array_unique($columns);

        $selectColumns = '';
        foreach ($columns as $column)
        {
            $selectColumns .= "$column as `$column`,";
        }
        $stmt = $this->pdo->prepare("SELECT DISTINCT " . rtrim($selectColumns, ',')
                                    . " FROM $table $innerJoinString"
                                    . " WHERE " . implode(' IS NOT NULL AND ', $columns) . " IS NOT NULL;"
                                    . " LIMIT :limit OFFSET :offset");
        $stmt->bindParam(':limit',  $limit,  \PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    public function selectEverythingFrom(string $table)
    {
        return $this->pdo->query("SELECT * FROM $table");
    }
}
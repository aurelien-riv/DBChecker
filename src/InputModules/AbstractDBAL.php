<?php

namespace DBChecker\InputModules;

use BadMethodCallException;

abstract class AbstractDBAL implements
    \DBChecker\modules\MissingCompressionDetect\DBQueriesInterface,
    \DBChecker\modules\DataIntegrityCheck\DBQueriesInterface,
    \DBChecker\modules\SchemaIntegrityCheck\DBQueriesInterface,
    \DBChecker\modules\FileCheck\DBQueriesInterface,
    \DBChecker\modules\MissingKeyDetect\DBQueriesInterface,
    \DBChecker\modules\RelCheck\DBQueriesInterface,
    \DBChecker\modules\FragmentationCheck\DBQueriesInterface,
    \DBChecker\modules\UniqueIntegrityCheck\DBQueriesInterface,
    \DBChecker\modules\AnalyzeTableCheck\DBQueriesInterface
{
    const NOT_IMPLEMENTED_ERROR_MSG = "This method is not available for this database";

    /**
     * @var AbstractDbQueries $queries
     */
    protected $queries;

    public function __construct(AbstractDbQueries $queries)
    {
        $this->queries = $queries;
    }

    public function __call($name, $arguments)
    {
        return $this->queries->{$name}($arguments);
    }

    public function getName() : string
    {
        return $this->queries->getName();
    }

    public function supportsTablespaceCompression() : bool
    {
        return false;
    }

    public function isTableCompressed(string $table) : bool
    {
        return false;
    }

    protected function getInnerJoinString(string $table, array $innerJoinColumns)
    {
        $joins = '';
        foreach (array_unique($innerJoinColumns) as $innerJoinColumn)
        {
            $relation = $this->getDistantTableAndColumnFromTableAndColumnFK($table, $innerJoinColumn);

            $joins .= "INNER JOIN {$relation['table']} AS $innerJoinColumn
                ON $innerJoinColumn.{$relation['column']} = $table.$innerJoinColumn ";
        }
        return $joins;
    }

    public function getDistinctValuesWithJoinColumnsWithoutNulls(string $table, array $columns, array $innerJoins, int $limit, int $offset) : array
    {
        $innerJoins = $this->getInnerJoinString($table, $innerJoins);
        return $this->queries->getDistinctValuesWithJoinColumnsWithoutNulls($table, $columns, $innerJoins, $limit, $offset)->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getTableNames() : array
    {
        throw new BadMethodCallException(static::NOT_IMPLEMENTED_ERROR_MSG);
    }

    public function getColumnNamesInTable(string $table) : array
    {
        throw new BadMethodCallException(static::NOT_IMPLEMENTED_ERROR_MSG);
    }

    public function getFks() : array
    {
        throw new BadMethodCallException(static::NOT_IMPLEMENTED_ERROR_MSG);
    }

    public function getPKs(string $table) : array
    {
        throw new BadMethodCallException(static::NOT_IMPLEMENTED_ERROR_MSG);
    }

    public function getDistinctValuesWithoutNulls(string $table, array $columns) : array
    {
        return $this->queries->getDistinctValuesWithoutNulls($table, $columns)->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function getValue(string $table, string $column, $value)
    {
        return $this->queries->getValue($table, $column, $value)->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function getTableDataSha1sum(string $table) : string
    {
        throw new BadMethodCallException(static::NOT_IMPLEMENTED_ERROR_MSG);
    }

    public function getTableSchemaSha1sum(string $table) : string
    {
        throw new BadMethodCallException(static::NOT_IMPLEMENTED_ERROR_MSG);
    }

    public function getFragmentedTables() : array
    {
        throw new BadMethodCallException(static::NOT_IMPLEMENTED_ERROR_MSG);
    }

    public function getTableLargerThanMb(int $minSize_MB) : array
    {
        throw new BadMethodCallException(static::NOT_IMPLEMENTED_ERROR_MSG);
    }

    public function getRandomValuesConcatenated(string $table, int $limit) : string
    {
        throw new BadMethodCallException(static::NOT_IMPLEMENTED_ERROR_MSG);
    }

    public function getDistantTableAndColumnFromTableAndColumnFK(string $table, string $column) : ?array
    {
        throw new BadMethodCallException(static::NOT_IMPLEMENTED_ERROR_MSG);
    }

    public function getUniqueIndexes(string $table) : array
    {
        throw new BadMethodCallException(static::NOT_IMPLEMENTED_ERROR_MSG);
    }

    public function analyzeTable(string $table) : \stdClass
    {
        throw new BadMethodCallException(static::NOT_IMPLEMENTED_ERROR_MSG);
    }

    /**
     * @param string $table
     * @param string $columns A coma separated list of columns
     * @return array
     *
     * If $columns references a single column (= the string contains no coma), NULL values won't be considered as
     * duplicates, otherwise a null column will be treated as a duplicate contrary to the SQL norm as it may be
     * not wanted.
     */
    public function getDuplicateForColumnsWithCount(string $table, string $columns) : array
    {
        return $this->queries->getDuplicateForColumnsWithCount($table, $columns)->fetchAll(\PDO::FETCH_OBJ);
    }
}
<?php

namespace DBChecker\DBQueries;

abstract class AbstractDbQueries implements
    \DBChecker\modules\MissingCompressionDetect\DBQueriesInterface,
    \DBChecker\modules\DataIntegrityCheck\DBQueriesInterface,
    \DBChecker\modules\SchemaIntegrityCheck\DBQueriesInterface,
    \DBChecker\modules\FileCheck\DBQueriesInterface,
    \DBChecker\modules\MissingKeyDetect\DBQueriesInterface,
    \DBChecker\modules\RelCheck\DBQueriesInterface
{
    /**
     * Regex that matches a valid column name
     */
    const IDENTIFIER = '[a-zA-Z_][a-zA-Z0-9_]*';

    protected $pdo;
    protected $name;

    public function __construct(\PDO $pdo, $name)
    {
        $this->pdo = $pdo;
        $this->name = $name;
    }

    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @param string $table
     * @return bool|\PDOStatement
     */
    public abstract function getUniqueIndexes($table);

    /**
     * @param string $table The table name
     * @param string $columns A coma separated list of columns
     * If $columns references a single column (= the string contains no coma), NULL values won't be considered as
     * duplicates, otherwise a null column will be treated as a duplicate contrary to the SQL norm as it may be
     * not wanted.
     */
    public abstract function getDuplicateForColumnsWithCount($table, $columns);

    public function supportsTablespaceCompression() : bool
    {
        return false;
    }

    public function isTableCompressed(string $table) : bool
    {
        return false;
    }
}
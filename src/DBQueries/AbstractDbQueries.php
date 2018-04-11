<?php

namespace DBChecker\DBQueries;

abstract class AbstractDbQueries implements
    \DBChecker\modules\MissingCompressionDetect\DBQueriesInterface,
    \DBChecker\modules\DataIntegrityCheck\DBQueriesInterface,
    \DBChecker\modules\SchemaIntegrityCheck\DBQueriesInterface,
    \DBChecker\modules\FileCheck\DBQueriesInterface,
    \DBChecker\modules\MissingKeyDetect\DBQueriesInterface,
    \DBChecker\modules\RelCheck\DBQueriesInterface,
    \DBChecker\modules\FragmentationCheck\DBQueriesInterface,
    \DBChecker\modules\UniqueIntegrityCheck\DBQueriesInterface
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

    public function supportsTablespaceCompression() : bool
    {
        return false;
    }

    public function isTableCompressed(string $table) : bool
    {
        return false;
    }
}
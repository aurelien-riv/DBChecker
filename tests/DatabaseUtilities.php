<?php

namespace DBCheckerTests;

use DBChecker\modules\ModuleManager;

trait DatabaseUtilities
{
    use BypassVisibilityTrait;

    public static function getSqliteMemoryConfig()
    {
        return [
            'dsn' => "sqlite::memory:",
            'name' => 'test',
            'engine' => 'sqlite'
        ];
    }
    public function getPdo(ModuleManager $moduleManager)
    {
        $dbal = $moduleManager->getDatabaseModule()->getDBALs()[0];
        $queries = $this->getAttributeValue($dbal, 'queries');
        return $this->getAttributeValue($queries, 'pdo');
    }
}
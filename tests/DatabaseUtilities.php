<?php

namespace DBCheckerTests;

use DBChecker\DBAL\MySQLDBAL;
use DBChecker\DBAL\SQLiteDBAL;
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

    public static function getMysqlConfig()
    {
        return [
            'db' => 'test',
            'host' => 'localhost',
            'port' => 3306,
            'login' => 'travis',
            'password' => null,
            'engine' => 'mysql'
        ];
    }

    public function getPdo(ModuleManager $moduleManager, $index=0)
    {
        $dbal = $moduleManager->getDatabaseModule()->getDBALs()[$index];
        $queries = $this->getAttributeValue($dbal, 'queries');
        return $this->getAttributeValue($queries, 'pdo');
    }

    public function cleanDbs(array $dbals)
    {
        foreach ($dbals as $dbal)
        {
            $queries = $this->getAttributeValue($dbal, 'queries');
            /** @var \PDO $pdo */
            $pdo = $this->getAttributeValue($queries, 'pdo');
            if ($dbal instanceof MySQLDBAL)
            {
                $this->cleanMySQL($dbal, $pdo);
            }
            else if ($dbal instanceof SQLiteDBAL)
            {
                continue; // in memory database, nothing to do
            }
        }
    }

    private function cleanMySQL(MySQLDBAL $dbal, \PDO $pdo)
    {
        $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
        foreach($dbal->getTableNames() as $table)
        {
            $pdo->exec("DROP TABLE $table");
        }
    }
}
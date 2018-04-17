<?php

namespace DBCheckerTests;

use DBChecker\DBAL\AbstractDBAL;
use DBChecker\DBAL\MsSQLDBAL;
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

    public function getPdo(ModuleManager $moduleManager)
    {
        $dbal = $moduleManager->getDatabaseModule()->getDBALs()[0];
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
                $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
                foreach($dbal->getTableNames() as $table)
                {
                    $pdo->exec("DROP TABLE $table");
                }
            }
            else if ($dbal instanceof SQLiteDBAL)
            {
                continue; // in memory database, nothing to do
            }
        }
    }
}
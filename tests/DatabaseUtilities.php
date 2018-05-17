<?php

namespace DBCheckerTests;

use DBChecker\InputModules\AbstractDBAL;
use DBChecker\InputModules\MySQL\MySQLDBAL;
use DBChecker\InputModules\SQLite\SQLiteDBAL;
use DBChecker\modules\ModuleManager;

trait DatabaseUtilities
{
    use BypassVisibilityTrait;

    public static function getSqliteMemoryConfig()
    {
        return [
            'db' => ":memory:",
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

    public function getDbal(ModuleManager $moduleManager, $index=0)
    {
        $dbals = iterator_to_array($moduleManager->getDatabaseModule()->getDBALs());
        return $dbals[$index];
    }

    public function getPdo(ModuleManager $moduleManager, $index=0)
    {
        $dbal = $this->getDbal($moduleManager, $index);
        $queries = $this->getAttributeValue($dbal, 'queries');
        return $this->getAttributeValue($queries, 'pdo');
    }

    /**
     * @param \Generator|AbstractDBAL[] $dbals
     */
    public function cleanDbs(\Generator $dbals)
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
<?php

namespace DBCheckerTests\modules\RelCheckTest;

use DBChecker\DBAL\AbstractDBAL;
use DBChecker\DBAL\MySQLDBAL;
use DBChecker\DBAL\SQLiteDBAL;
use DBChecker\modules\DataBase\DatabasesModule;
use DBChecker\modules\ModuleManager;
use DBChecker\modules\RelCheck\RelCheck;
use DBChecker\modules\UniqueIntegrityCheck\UniqueIntegrityCheckMatch;
use DBChecker\modules\UniqueIntegrityCheck\UniqueIntegrityCheckModule;
use DBCheckerTests\DatabaseUtilities;

class UniqueIntegrityCheck extends \PHPUnit\Framework\TestCase
{
    use DatabaseUtilities;

    /**
     * @var ModuleManager
     */
    private $moduleManager;

    public function setUp()
    {
        parent::setUp();
        $settings = [
            'databases' => [
                'connections' => [
                    $this->getSqliteMemoryConfig(),
                    $this->getMysqlConfig()
                ]
            ],
            'uniqueintegritycheck' => []
        ];
        $this->moduleManager = new ModuleManager();
        foreach ([DatabasesModule::class, UniqueIntegrityCheckModule::class] as $module)
        {
            $this->moduleManager->loadModule(new $module(), $settings);
        }
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->cleanDbs($this->moduleManager->getDatabaseModule()->getDBALs());
    }

    private function init($dbIndex)
    {
        $dbal = $this->moduleManager->getDatabaseModule()->getDBALs()[$dbIndex];
        $queries = $this->getAttributeValue($dbal, 'queries');
        $pdo = $this->getAttributeValue($queries, 'pdo');
        $this->initDb($dbal, $pdo);
        return $dbal;
    }

    private function initDb(AbstractDBAL $dbal, \PDO $pdo)
    {
        $pdo->exec("CREATE TABLE t1 (id INTEGER PRIMARY KEY, data1 CHAR(2), data2 CHAR(2));");
        $pdo->exec("CREATE UNIQUE INDEX t2_data_unique ON t1 (data1, data2);");
        $pdo->exec("INSERT INTO t1 VALUES (1, 'v1', NULL);");
        $pdo->exec("INSERT INTO t1 VALUES (2, 'v1', NULL);");

    }

    #region SQLite
    public function testSQLite()
    {
        $dbal = $this->init(0);
        /** @var UniqueIntegrityCheck $uniqueCheck */
        $uniqueCheck = $this->moduleManager->getWorkers()->current();
        $generator = $uniqueCheck->run($dbal);

        /** @var UniqueIntegrityCheckMatch $match */
        $match = $generator->current();
        $this->assertInstanceOf(UniqueIntegrityCheckMatch::class, $match);
        $this->assertEquals(2, $match->getCount());
        $this->assertEquals(
            ['data1' => 'v1', 'data2' => null],
            $match->getValues(),
            'Contrary to the SQL norm, we consider null duplicates involving NULL are real duplicates'
        );

        $generator->next();
        $this->assertNull($generator->current());
    }
    #endregion

    #region MySQL
    public function testMySQL()
    {
        $dbal = $this->init(1);
        /** @var UniqueIntegrityCheck $uniqueCheck */
        $uniqueCheck = $this->moduleManager->getWorkers()->current();
        $generator = $uniqueCheck->run($dbal);

        /** @var UniqueIntegrityCheckMatch $match */
        $match = $generator->current();
        $this->assertInstanceOf(UniqueIntegrityCheckMatch::class, $match);
        $this->assertEquals(2, $match->getCount());
        $this->assertEquals(
            ['data1' => 'v1', 'data2' => null],
            $match->getValues(),
            'Contrary to the SQL norm, we consider null duplicates involving NULL are real duplicates'
        );

        $generator->next();
        $this->assertNull($generator->current());
    }
    #endregion
}
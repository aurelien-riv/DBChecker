<?php

namespace DBCheckerTests\modules\RelCheckTest;

use DBChecker\DBAL\AbstractDBAL;
use DBChecker\DBAL\MsSQLDBAL;
use DBChecker\DBAL\MySQLDBAL;
use DBChecker\DBAL\SQLiteDBAL;
use DBChecker\modules\DataBase\DatabasesModule;
use DBChecker\modules\ModuleManager;
use DBChecker\modules\RelCheck\RelCheckMatch;
use DBChecker\modules\RelCheck\RelCheckModule;
use DBCheckerTests\DatabaseUtilities;

class RelCheckTest extends \PHPUnit\Framework\TestCase
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
            'relcheck' => []
        ];
        $this->moduleManager = new ModuleManager();
        foreach ([DatabasesModule::class, RelCheckModule::class] as $module)
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
        $pdo->exec("CREATE TABLE t1 (id INTEGER PRIMARY KEY);");
        $pdo->exec("CREATE TABLE t2 (id INTEGER PRIMARY KEY, FOREIGN KEY (id) REFERENCES t1(id));");
        if ($dbal instanceof SQLiteDBAL)
        {
            $pdo->exec("PRAGMA foreign_keys = OFF");
        }
        else if ($dbal instanceof MsSQLDBAL)
        {
            $pdo->exec("ALTER TABLE t2 NOCHECK CONSTRAINT all");
        }
        else if ($dbal instanceof MySQLDBAL)
        {
            $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
        }
        $pdo->exec("INSERT INTO t2 VALUES (1)");
    }

    public function testRelCheckSQLite()
    {
        $dbal = $this->init(0);
        $relcheck = $this->moduleManager->getWorkers()->current();
        $this->assertInstanceOf(RelCheckMatch::class, $relcheck->run($dbal)->current());
    }
    public function testRelCheckMySQL()
    {
        $dbal = $this->init(1);
        $relcheck = $this->moduleManager->getWorkers()->current();
        $this->assertInstanceOf(RelCheckMatch::class, $relcheck->run($dbal)->current());
    }
}
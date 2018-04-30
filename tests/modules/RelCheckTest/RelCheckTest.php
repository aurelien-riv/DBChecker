<?php

namespace DBCheckerTests\modules\RelCheckTest;

use DBChecker\DBAL\AbstractDBAL;
use DBChecker\DBAL\MySQLDBAL;
use DBChecker\DBAL\SQLiteDBAL;
use DBChecker\modules\DataBase\DatabasesModule;
use DBChecker\modules\ModuleManager;
use DBChecker\modules\RelCheck\ColumnNotFoundMatch;
use DBChecker\modules\RelCheck\RelCheck;
use DBChecker\modules\RelCheck\RelCheckMatch;
use DBChecker\modules\RelCheck\RelCheckModule;
use DBChecker\modules\RelCheck\TableNotFoundMatch;
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

    private function getWorker($dbIndex, &$dbal) : RelCheck
    {
        $dbal = $this->init($dbIndex);
        /** @var RelCheck $relcheck */
        $relcheck = $this->moduleManager->getWorkers()->current();
        $this->callMethod($relcheck, "init", [$dbal]);
        return $relcheck;
    }

    private function initDb(AbstractDBAL $dbal, \PDO $pdo)
    {
        $pdo->exec("CREATE TABLE t1 (t1_id INTEGER PRIMARY KEY);");
        $pdo->exec("CREATE TABLE t2 (t2_id INTEGER PRIMARY KEY, FOREIGN KEY (t2_id) REFERENCES t1(t1_id));");
        $pdo->exec("CREATE TABLE t3 (t3_id INTEGER PRIMARY KEY);");
        $pdo->exec("CREATE TABLE t4 (t4_id INTEGER PRIMARY KEY, FOREIGN KEY (t4_id) REFERENCES t3(t3_id));");
        if ($dbal instanceof SQLiteDBAL)
        {
            $pdo->exec("PRAGMA foreign_keys = OFF");
        }
        else if ($dbal instanceof MySQLDBAL)
        {
            $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
        }
        $pdo->exec("DROP TABLE t3;");
        $pdo->exec("INSERT INTO t2 VALUES (1)");
    }

    private function doTestRelCheck(AbstractDBAL $dbal)
    {
        /** @var RelCheck $relcheck */
        $relcheck = $this->moduleManager->getWorkers()->current();
        $generator = $relcheck->run($dbal);

        /** @var RelCheckMatch $match */
        $match = $generator->current();
        $this->assertInstanceOf(RelCheckMatch::class, $match);
        $this->assertEquals('t1', $match->getReferencedTable());
        $this->assertEquals('t2', $match->getTable());
        $this->assertEquals('t1_id', $match->getReferencedColumn());
        $this->assertEquals('t2_id', $match->getColumn());
        $this->assertEquals('1', $match->getValue());
        $generator->next();

        /** @var TableNotFoundMatch $match */
        $match = $generator->current();
        $this->assertInstanceOf(TableNotFoundMatch::class, $match);
        $this->assertEquals('t3', $match->getTable());

        $generator->next();
        $this->assertNull($generator->current());
    }

    #region SQLite
    public function testRelCheckSQLite()
    {
        $this->doTestRelCheck($this->init(0));
    }

    public function testCheckSchema_SQLite()
    {
        $relcheck = $this->getWorker(0, $dbal);
        $generator = $relcheck->checkSchema($dbal, "t1", "t1_id");
        $this->assertNull($generator->current());
        $generator->next();
        $this->assertNull($generator->current());
    }
    public function testCheckSchema_UnknownTable_UnknownColumn_SQLite()
    {
        $relcheck = $this->getWorker(0, $dbal);
        $generator = $relcheck->checkSchema($dbal, "unknown", "unknown");
        $this->assertInstanceOf(TableNotFoundMatch::class, $generator->current());
        $generator->next();
        $this->assertNull($generator->current());
    }
    public function testCheckSchema_UnknownTable_SQLite()
    {
        $relcheck = $this->getWorker(0, $dbal);
        $generator = $relcheck->checkSchema($dbal, "unknown", "id");
        $this->assertInstanceOf(TableNotFoundMatch::class, $generator->current());
        $generator->next();
        $this->assertNull($generator->current());
    }
    public function testCheckSchema_UnknownColumn_SQLite()
    {
        $relcheck = $this->getWorker(0, $dbal);
        $generator = $relcheck->checkSchema($dbal, "t2", "unknown");
        $this->assertInstanceOf(ColumnNotFoundMatch::class, $generator->current());
        $generator->next();
        $this->assertNull($generator->current());
    }
    #endregion

    #region MySQL
    public function testRelCheckMySQL()
    {
        $this->doTestRelCheck($this->init(1));
    }
    public function testCheckSchema_MySQL()
    {
        $relcheck = $this->getWorker(1, $dbal);
        $this->assertNull($relcheck->checkSchema($dbal, "t1", "t1_id")->current());
    }
    public function testCheckSchema_UnknownTable_UnknownColumn_MySQL()
    {
        $relcheck = $this->getWorker(1, $dbal);
        $generator = $relcheck->checkSchema($dbal, "unknown", "unknown");
        $this->assertInstanceOf(TableNotFoundMatch::class, $generator->current());
        $generator->next();
        $this->assertNull($generator->current());

    }
    public function testCheckSchema_UnknownTable_MySQL()
    {
        $relcheck = $this->getWorker(1, $dbal);
        $generator = $relcheck->checkSchema($dbal, "unknown", "id");
        $this->assertInstanceOf(TableNotFoundMatch::class, $generator->current());
        $generator->next();
        $this->assertNull($generator->current());
    }
    public function testCheckSchema_UnknownColumn_MySQL()
    {
        $relcheck = $this->getWorker(1, $dbal);
        $generator = $relcheck->checkSchema($dbal, "t2", "unknown");
        $this->assertInstanceOf(ColumnNotFoundMatch::class, $generator->current());
        $generator->next();
        $this->assertNull($generator->current());
    }
    #endregion
}
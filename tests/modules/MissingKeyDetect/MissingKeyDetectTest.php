<?php

namespace DBCheckerTests\modules\MissingCompressionDetect;

use DBChecker\DBAL\AbstractDBAL;
use DBChecker\modules\DataBase\DatabasesModule;
use DBChecker\modules\FileCheck\FileCheckModule;
use DBChecker\modules\MissingKeyDetect\MissingKeyDetect;
use DBChecker\modules\MissingKeyDetect\MissingKeyDetectMatch;
use DBChecker\modules\MissingKeyDetect\MissingKeyDetectModule;
use DBChecker\modules\ModuleManager;
use DBCheckerTests\DatabaseUtilities;

final class MissingKeyDetectTest extends \PHPUnit\Framework\TestCase
{
    use DatabaseUtilities;

    /**
     * @var ModuleManager $module
     */
    private $moduleManager;

    public function setUp()
    {
        parent::setUp();
        $this->moduleManager = new ModuleManager();
        $settings = [
            'databases' => [
                'connections' => [
                    $this->getSqliteMemoryConfig(),
                    $this->getMysqlConfig()
                ]
            ],
            'missingkeydetect' => [
                'patterns' => [
                    '_id$'
                ]
            ]
        ];
        $this->moduleManager = new ModuleManager();
        $this->moduleManager->loadModule(new DatabasesModule(), $settings);
        $module = new MissingKeyDetectModule();
        $this->moduleManager->loadModule($module, [
            $module->getName() => $settings[$module->getName()]
        ]);
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->cleanDbs($this->moduleManager->getDatabaseModule()->getDBALs());
    }

    private function init($dbIndex) : AbstractDBAL
    {
        $dbal = $this->moduleManager->getDatabaseModule()->getDBALs()[$dbIndex];
        $queries = $this->getAttributeValue($dbal, 'queries');
        $pdo = $this->getAttributeValue($queries, 'pdo');
        $this->initDb($pdo);
        return $dbal;
    }

    private function initDb(\PDO $pdo)
    {
        $pdo->exec("CREATE TABLE t1 (t1_id INTEGER PRIMARY KEY, data INTEGER);");
        $pdo->exec("CREATE TABLE t2 (t2_id INTEGER PRIMARY KEY, t1_id INTEGER, FOREIGN KEY (t1_id) REFERENCES t1(t1_id));");
        $pdo->exec("CREATE TABLE t3 (t3_id INTEGER PRIMARY KEY, t2_id INTEGER);");
    }

    public function getInstanceWithEmptyConfig()
    {
        $module = new MissingKeyDetectModule();
        $this->moduleManager->loadModule($module, [$module->getName() => ['patterns' => ['']]]);
        return $this->moduleManager->getWorkers()->current();
    }

    #region split
    public function testSplit_CamelCase()
    {
        $data = $this->getInstanceWithEmptyConfig()->split("TestCamelCase");
        $this->assertEquals(['Test', 'Camel', 'Case'], $data);
    }
    public function testSplit_mixedCamelCase()
    {
        $data = $this->getInstanceWithEmptyConfig()->split("testCamelCase");
        $this->assertEquals(['test', 'Camel', 'Case'], $data);
    }
    public function testSplit_CamelCaseWithNumbers()
    {
        $data = $this->getInstanceWithEmptyConfig()->split("Test456CamelCase");
        $this->assertEquals(['Test456', 'Camel', 'Case'], $data);
    }
    public function testSplit_mixedCamelCaseWithNumber()
    {
        $data = $this->getInstanceWithEmptyConfig()->split("test456CamelCase");
        $this->assertEquals(['test456', 'Camel', 'Case'], $data);
    }
    #endregion

    private function doTestRun($dbIndex)
    {
        /** @var MissingKeyDetect $worker */
        $worker = $this->moduleManager->getWorkers()->current();
        $generator = $worker->run($this->init($dbIndex));
        /** @var MissingKeyDetectMatch $match */
        $match = $generator->current();
        $this->assertInstanceOf(MissingKeyDetectMatch::class, $match);
        $this->assertEquals('t2_id', $match->getColumn());
        $this->assertEquals('t3', $match->getTable());
        $generator->next();
        $this->assertNull($generator->current());
    }

    public function testRunSQLite()
    {
        $this->doTestRun(0);
    }

    public function testRunMySQL()
    {
        $this->doTestRun(1);
    }

    public function testRunWithPatterns()
    {
        $module = new MissingKeyDetectModule();
        $this->moduleManager->loadModule($module, [$module->getName() => [
            'patterns' => [
                '_id$'
            ]
        ]]);
        $instance = $this->moduleManager->getWorkers()->current();
        $data = $this->callMethod($instance, 'runWithPatterns', ["", [
            ['', 'something'],
            ['', 'something_id'],
            ['', 'id'],
            ['', '_id'],
            ['', 'id_something']
        ]]);
        $data = iterator_to_array($data);

        $this->assertEquals(2, count($data));
        foreach ($data as $datum) /** @var MissingKeyDetectMatch $datum */
        {
            $this->assertInstanceOf(MissingKeyDetectMatch::class, $datum);
            $this->assertContains($datum->getColumn(), ['something_id', '_id']);
        }
    }
}

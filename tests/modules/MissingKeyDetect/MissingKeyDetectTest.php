<?php

namespace DBCheckerTests\modules\MissingCompressionDetect;

use DBChecker\modules\DataBase\DatabasesModule;
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
            'missingkeydetect' => []
        ];
        $this->moduleManager = new ModuleManager();
        $this->moduleManager->loadModule(new DatabasesModule(), $settings);
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
        $this->moduleManager->loadModule($module, [$module->getName() => []]);
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

    public function testRun_AutoDetect_SQLite()
    {
        $relcheck = $this->getInstanceWithEmptyConfig();
        $dbal = $this->init(0);

        /** @var \Generator $generator */
        $generator = $relcheck->run($dbal);

        /** @var MissingKeyDetectMatch $error */
        $error = $generator->current();
        $this->assertInstanceOf(MissingKeyDetectMatch::class, $error);
        $this->assertEquals($error->getTable(), "t3");
        $this->assertEquals($error->getColumn(), "t2_id");

        $generator->next();
        $this->assertNull($generator->current());
    }
    public function testRun_AutoDetect_MySQL()
    {
        $relcheck = $this->getInstanceWithEmptyConfig();
        $dbal = $this->init(1);

        /** @var \Generator $generator */
        $generator = $relcheck->run($dbal);

        /** @var MissingKeyDetectMatch $error */
        $error = $generator->current();
        $this->assertInstanceOf(MissingKeyDetectMatch::class, $error);
        $this->assertEquals($error->getTable(), "t3");
        $this->assertEquals($error->getColumn(), "t2_id");

        $generator->next();
        $this->assertNull($generator->current());
    }
}

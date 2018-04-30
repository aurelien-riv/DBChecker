<?php

namespace DBCheckerTests\modules\SchemaIntegrity;

use DBChecker\modules\DataBase\DatabasesModule;
use DBChecker\modules\ModuleManager;
use DBChecker\modules\SchemaIntegrityCheck\SchemaIntegrityCheck;
use DBChecker\modules\SchemaIntegrityCheck\SchemaIntegrityCheckMatch;
use DBChecker\modules\SchemaIntegrityCheck\SchemaIntegrityCheckModule;
use DBCheckerTests\DatabaseUtilities;
use Symfony\Component\Yaml\Yaml;

class SchemaIntegrityTest extends \PHPUnit\Framework\TestCase
{
    use DatabaseUtilities;

    /**
     * @var ModuleManager $module
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
            ]
        ];
        $this->moduleManager = new ModuleManager();
        $this->moduleManager->loadModule(new DatabasesModule(), $settings);
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->cleanDbs($this->moduleManager->getDatabaseModule()->getDBALs());
    }

    private function initDBal($dbIndex)
    {
        $dbal = $this->moduleManager->getDatabaseModule()->getDBALs()[$dbIndex];
        $queries = $this->getAttributeValue($dbal, 'queries');
        $pdo = $this->getAttributeValue($queries, 'pdo');
        $pdo->exec("CREATE TABLE t1 (id INTEGER PRIMARY KEY, data VARCHAR(64));");
        $pdo->exec("INSERT INTO t1 VALUES (1, 'this is a test')");
        $pdo->exec("CREATE TABLE ignore_me (id INTEGER PRIMARY KEY);");
        return $dbal;
    }

    private function getWorker($config) : SchemaIntegrityCheck
    {
        $config = Yaml::parse($config);
        $module = new SchemaIntegrityCheckModule();
        $this->moduleManager->loadModule($module, [
            $module->getName() => $config[$module->getName()]
        ]);
        return $this->moduleManager->getWorkers()->current();
    }

    #region SQLite
    public function testGenerateConfigSQlite()
    {
        $dbal = $this->initDBal(0);
        $worker = $this->getWorker("schemaintegritycheck:\n  mapping:\n    - t1: null\n  ignore:\n    - 'ignore_.*'\n");
        $config = $worker->generateConfig($dbal);
        $this->assertEquals("schemaintegritycheck:\n  ignore:\n    - 'ignore_.*'\n  mapping:\n    - t1: f0443bd47ea6c32ab2beea43efeadaf3aee9b4f5\n", $config);
        return $config;
    }

    /**
     * @depends testGenerateConfigSQlite
     * @param string $config
     */
    public function testRunWithoutChangesSQlite(string $config)
    {
        $dbal = $this->initDBal(0);
        $worker = $this->getWorker($config);
        $this->assertNull($worker->run($dbal)->current());
    }

    /**
     * @depends testGenerateConfigSQlite
     * @param string $config
     */
    public function testRunWithDataChangesSQlite(string $config)
    {
        $dbal = $this->initDBal(0);
        $pdo = $this->getPdo($this->moduleManager, 0);
        $pdo->exec("UPDATE t1 SET data = 'data changed'");
        $worker = $this->getWorker($config);
        $this->assertNull($worker->run($dbal)->current());
    }

    /**
     * @depends testGenerateConfigSQlite
     * @param string $config
     */
    public function testRunWithSchemaChangesSQlite(string $config)
    {
        $dbal = $this->initDBal(0);
        $pdo = $this->getPdo($this->moduleManager, 0);
        $pdo->exec("ALTER TABLE t1 ADD COLUMN new VARCHAR(2)");
        $worker = $this->getWorker($config);
        $this->assertInstanceOf(SchemaIntegrityCheckMatch::class, $worker->run($dbal)->current());
    }

    /**
     * @depends testGenerateConfigSQlite
     * @param string $config
     */
    public function testRunWithUnknownTableSQlite(string $config)
    {
        $dbal = $this->initDBal(0);
        $pdo = $this->getPdo($this->moduleManager, 0);
        $pdo->exec("CREATE TABLE t2 (id INTEGER PRIMARY KEY);");
        $worker = $this->getWorker($config);
        $this->assertInstanceOf(SchemaIntegrityCheckMatch::class, $worker->run($dbal)->current());
    }
    #endregion
    
    #region MySQL
    public function testGenerateConfigMySQL()
    {
        $dbal = $this->initDBal(1);
        $worker = $this->getWorker("schemaintegritycheck:\n  mapping:\n    - t1: null\n  ignore:\n    - 'ignore_.*'\n");
        $config = $worker->generateConfig($dbal);
        $this->assertEquals("schemaintegritycheck:\n  ignore:\n    - 'ignore_.*'\n  mapping:\n    - t1: f0443bd47ea6c32ab2beea43efeadaf3aee9b4f5\n", $config);
        return $config;
    }

    /**
     * @depends testGenerateConfigMySQL
     * @param string $config
     */
    public function testRunWithoutChangesMySQL(string $config)
    {
        $dbal = $this->initDBal(1);
        $worker = $this->getWorker($config);
        $this->assertNull($worker->run($dbal)->current());
    }

    /**
     * @depends testGenerateConfigMySQL
     * @param string $config
     */
    public function testRunWithDataChangesMySQL(string $config)
    {
        $dbal = $this->initDBal(1);
        $pdo = $this->getPdo($this->moduleManager, 1);
        $pdo->exec("UPDATE t1 SET data = 'data changed'");
        $worker = $this->getWorker($config);
        $this->assertNull($worker->run($dbal)->current());
    }

    /**
     * @depends testGenerateConfigMySQL
     * @param string $config
     */
    public function testRunWithSchemaChangesMySQL(string $config)
    {
        $dbal = $this->initDBal(1);
        $pdo = $this->getPdo($this->moduleManager, 1);
        $pdo->exec("ALTER TABLE t1 ADD COLUMN new VARCHAR(2)");
        $worker = $this->getWorker($config);
        $this->assertInstanceOf(SchemaIntegrityCheckMatch::class, $worker->run($dbal)->current());
    }

    /**
     * @depends testGenerateConfigMySQL
     * @param string $config
     */
    public function testRunWithUnknownTableMySQL(string $config)
    {
        $dbal = $this->initDBal(1);
        $pdo = $this->getPdo($this->moduleManager, 1);
        $pdo->exec("CREATE TABLE t2 (id INTEGER PRIMARY KEY);");
        $worker = $this->getWorker($config);
        $this->assertInstanceOf(SchemaIntegrityCheckMatch::class, $worker->run($dbal)->current());
    }
    #endregion
}

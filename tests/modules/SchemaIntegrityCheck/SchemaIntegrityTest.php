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
    private $dbal;

    public function setUp()
    {
        parent::setUp();

        $this->initModules();
        $this->initDb($this->getPdo($this->moduleManager));
    }

    private function initModules()
    {
        $this->moduleManager = new ModuleManager();
        $settings = [
            'databases' => [
                'connections' => [$this->getSqliteMemoryConfig()]
            ]
        ];
        $this->moduleManager = new ModuleManager();
        $this->moduleManager->loadModule(new DatabasesModule(), $settings);
        $this->dbal = $this->moduleManager->getDatabaseModule()->getDBALs()[0];
    }

    private function initDb(\PDO $pdo)
    {
        $pdo->exec("CREATE TABLE t1 (id INTEGER PRIMARY KEY, data VARCHAR(10));");
        $pdo->exec("INSERT INTO t1 VALUES (1, 'this is a test')");
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

    public function testGenerateConfig()
    {
        $schemaIntegrity = new SchemaIntegrityCheck(new SchemaIntegrityCheckModule());
        $config = $schemaIntegrity->generateConfig($this->dbal);
        $this->assertEquals("schemaintegritycheck:\n  mapping:\n    - t1: f0443bd47ea6c32ab2beea43efeadaf3aee9b4f5\n", $config);
        return $config;
    }

    /**
     * @depends testGenerateConfig
     * @param string $config
     */
    public function testRunWithoutChanges(string $config)
    {
        $worker = $this->getWorker($config);
        $this->assertNull($worker->run($this->dbal)->current());
    }

    /**
     * @depends testGenerateConfig
     * @param string $config
     */
    public function testRunWithDataChanges(string $config)
    {
        $pdo = $this->getPdo($this->moduleManager);
        $pdo->exec("UPDATE t1 SET data = 'data changed'");
        $worker = $this->getWorker($config);
        $this->assertNull($worker->run($this->dbal)->current());
    }

    /**
     * @depends testGenerateConfig
     * @param string $config
     */
    public function testRunWithSchemaChanges(string $config)
    {
        $pdo = $this->getPdo($this->moduleManager);
        $pdo->exec("ALTER TABLE t1 ADD COLUMN new VARCHAR(2)");
        $worker = $this->getWorker($config);
        $this->assertInstanceOf(SchemaIntegrityCheckMatch::class, $worker->run($this->dbal)->current());
    }

    /**
     * @depends testGenerateConfig
     * @param string $config
     */
    public function testRunWithUnknownTable(string $config)
    {
        $pdo = $this->getPdo($this->moduleManager);
        $pdo->exec("CREATE TABLE t2 (id INTEGER PRIMARY KEY);");
        $worker = $this->getWorker($config);
        $this->assertInstanceOf(SchemaIntegrityCheckMatch::class, $worker->run($this->dbal)->current());
    }
}

<?php

namespace DBCheckerTests\modules\DataIntegrity;

use DBChecker\modules\DataBase\DatabasesModule;
use DBChecker\modules\DataIntegrityCheck\DataIntegrityCheck;
use DBChecker\modules\DataIntegrityCheck\DataIntegrityCheckMatch;
use DBChecker\modules\DataIntegrityCheck\DataIntegrityCheckModule;
use DBChecker\modules\ModuleManager;
use DBCheckerTests\DatabaseUtilities;
use Symfony\Component\Yaml\Yaml;

class DataIntegrityTest extends \PHPUnit\Framework\TestCase
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

    private function getWorker($config) : DataIntegrityCheck
    {
        $config = Yaml::parse($config);
        $module = new DataIntegrityCheckModule();
        $this->moduleManager->loadModule($module, [
            $module->getName() => $config[$module->getName()]
        ]);
        return $this->moduleManager->getWorkers()->current();
    }

    public function testGenerateConfig()
    {
        $dataIntegrity = new DataIntegrityCheck(new DataIntegrityCheckModule());
        $config = $dataIntegrity->generateConfig($this->dbal);
        $this->assertEquals("dataintegritycheck:\n  mapping:\n    - t1: c2e264132bcc1243f127db9a1398a87a1ae7b9eb\n", $config);
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
    public function testRunWithChanges(string $config)
    {
        $pdo = $this->getPdo($this->moduleManager);
        $pdo->exec("INSERT INTO t1 VALUES (2, 'this is another test')");
        $worker = $this->getWorker($config);
        $this->assertInstanceOf(DataIntegrityCheckMatch::class, $worker->run($this->dbal)->current());
    }
}

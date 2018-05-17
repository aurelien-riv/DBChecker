<?php

namespace DBCheckerTests\modules\DataIntegrity;

use DBChecker\InputModules\InputModuleManager;
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
        $this->moduleManager->loadModule(new InputModuleManager(), $settings);
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->cleanDbs($this->moduleManager->getDatabaseModule()->getDBALs());
    }

    private function initDBal($dbIndex)
    {
        $dbals = $this->moduleManager->getDatabaseModule()->getDBALs();
        $dbal = iterator_to_array($dbals)[$dbIndex];
        $queries = $this->getAttributeValue($dbal, 'queries');
        $pdo = $this->getAttributeValue($queries, 'pdo');
        $pdo->exec("CREATE TABLE t1 (id INTEGER PRIMARY KEY, data VARCHAR(64));");
        $pdo->exec("INSERT INTO t1 VALUES (1, 'this is a test')");
        return $dbal;
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

    #region SQLite
    public function testGenerateConfigSQLite()
    {
        $dbal = $this->initDBal(0);
        $dataIntegrity = new DataIntegrityCheck(new DataIntegrityCheckModule());
        $config = $dataIntegrity->generateConfig($dbal);
        $this->assertEquals("dataintegritycheck:\n  mapping:\n    - t1: c2e264132bcc1243f127db9a1398a87a1ae7b9eb\n", $config);
        return $config;
    }

    /**
     * @depends testGenerateConfigSQLite
     * @param string $config
     */
    public function testRunWithoutChangesSQLite(string $config)
    {
        $dbal = $this->initDBal(0);
        $worker = $this->getWorker($config);
        $this->assertNull($worker->run($dbal)->current());
    }

    /**
     * @depends testGenerateConfigSQLite
     * @param string $config
     */
    public function testRunWithChangesSQLite(string $config)
    {
        $dbal = $this->initDBal(0);
        $pdo = $this->getPdo($this->moduleManager, 0);
        $pdo->exec("INSERT INTO t1 VALUES (2, 'this is another test')");
        $worker = $this->getWorker($config);
        $this->assertInstanceOf(DataIntegrityCheckMatch::class, $worker->run($dbal)->current());
    }
    #endregion
    #region MySQL
    public function testGenerateConfigMySQL()
    {
        $dbal = $this->initDBal(1);
        $dataIntegrity = new DataIntegrityCheck(new DataIntegrityCheckModule());
        $config = $dataIntegrity->generateConfig($dbal);
        $this->assertEquals("dataintegritycheck:\n  mapping:\n    - t1: c2e264132bcc1243f127db9a1398a87a1ae7b9eb\n", $config);
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
    public function testRunWithChangesMySQL(string $config)
    {
        $dbal = $this->initDBal(1);
        $pdo = $this->getPdo($this->moduleManager, 1);
        $pdo->exec("INSERT INTO t1 VALUES (2, 'this is another test')");
        $worker = $this->getWorker($config);
        $this->assertInstanceOf(DataIntegrityCheckMatch::class, $worker->run($dbal)->current());
    }
    #endregion
}

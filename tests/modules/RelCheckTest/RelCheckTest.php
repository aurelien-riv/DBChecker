<?php

namespace DBCheckerTests\modules\RelCheckTest;

use DBChecker\DBAL\SQLiteDBAL;
use DBChecker\DBQueries\SQLiteQueries;
use DBChecker\modules\DataBase\DatabasesModule;
use DBChecker\modules\ModuleManager;
use DBChecker\modules\RelCheck\RelCheck;
use DBChecker\modules\RelCheck\RelCheckMatch;
use DBChecker\modules\RelCheck\RelCheckModule;
use DBCheckerTests\BypassVisibilityTrait;

class RelCheckTest extends \PHPUnit\Framework\TestCase
{
    use BypassVisibilityTrait;

    /**
     * @var ModuleManager
     */
    private $moduleManager;
    /**
     * @var SQLiteDBAL
     */
    private $dbal;

    public function setUp()
    {
        parent::setUp();
        $this->initModules();
        $this->dbal = $this->moduleManager->getDatabaseModule()->getDBALs()[0];
        $queries = $this->getAttributeValue($this->dbal, 'queries');
        $pdo = $this->getAttributeValue($queries, 'pdo');
        $this->initDb($pdo);
    }

    private function initModules()
    {
        $settings = [
            'databases' => [
                'connections' => [
                    [
                        'dsn' => "sqlite::memory:",
                        'name' => 'test',
                        'engine' => 'sqlite'
                    ]
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

    private function initDb(\PDO $pdo)
    {
        $pdo->exec("CREATE TABLE t1 (id INTEGER PRIMARY KEY);");
        $pdo->exec("CREATE TABLE t2 (id INTEGER PRIMARY KEY, FOREIGN KEY (id) REFERENCES t1(id));");
        $pdo->exec("PRAGMA foreign_keys = OFF");
        $pdo->exec("INSERT INTO t2 VALUES (1)");
    }

    public function testRelCheck()
    {
        /** @var RelCheck $relcheck */
        $relcheck = $this->moduleManager->getWorkers()->current();
        $this->assertInstanceOf(RelCheckMatch::class, $relcheck->run($this->dbal)->current());
    }
}
<?php

namespace DBCheckerTests\modules\MissingCompressionDetect;

use DBChecker\Config;
use DBChecker\modules\ModuleManager;

final class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Config $config
     */
    private $config;

    public function setUp()
    {
        parent::setUp();
        $this->config = new Config(__DIR__.'/../config.template.yaml');
    }

    public function testDatabase()
    {
        $queries = $this->config->getDBALs();
        $this->assertEquals(2, count($queries));
    }

    public function testWorkers()
    {
        $workers = iterator_to_array($this->config->getModuleWorkers());
        // -1 to exclude DatabaseModule
        $expected = count(ModuleManager::ENABLED_MODULES)-1;
        $this->assertGreaterThanOrEqual($expected, count($workers));
    }
}

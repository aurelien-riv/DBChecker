<?php

namespace DBCheckerTests\modules\FileCheck;

use DBChecker\modules\FileCheck\FileCheckMatch;
use DBChecker\modules\FileCheck\FileCheckModule;
use DBChecker\modules\FileCheck\FileCheckURLMatch;
use DBChecker\modules\ModuleManager;
use DBCheckerTests\BypassVisibilityTrait;

class FileCheckTest extends \PHPUnit\Framework\TestCase
{
    use BypassVisibilityTrait;

    /**
     * @var ModuleManager $module
     */
    private $moduleManager;

    public function setUp()
    {
        parent::setUp();
        $this->moduleManager = new ModuleManager();
    }

    public function getInstanceWithEmptyConfig()
    {
        $module = new FileCheckModule();
        $this->moduleManager->loadModule($module, [$module->getName() => [
            'mapping' => [
                []
            ]
        ]]);
        return $this->moduleManager->getWorkers()->current();
    }

    public function testTestFile_URL_RemoteDisabled()
    {
        $instance = $this->getInstanceWithEmptyConfig();
        $this->assertNull($this->callMethod($instance, 'testFile', ['', '', '', 'http://github.com'])->current());
    }
    public function testTestFile_URL_RemoteEnabled()
    {
        $module = new FileCheckModule();
        $this->moduleManager->loadModule($module, [$module->getName() => [
            'enable_remotes' => true,
            'mapping' => [
                []
            ]
        ]]);
        $instance = $this->moduleManager->getWorkers()->current();
        $this->assertInstanceOf(
            FileCheckURLMatch::class,
            $this->callMethod($instance, 'testFile', ['', '', '', 'http://does_not_exist.com'])->current()
        );
    }

    public function testTestFile_File()
    {
        $instance = $this->getInstanceWithEmptyConfig();
        $method = $this->getMethod($instance, 'testFile');
        $this->assertNull($method->invokeArgs($instance, ['', '', '', __DIR__.'/FileCheckTest.php'])->current());
        $this->assertInstanceOf(
            FileCheckMatch::class,
            $method->invokeArgs($instance, ['', '', '', 'does_not_exist'])->current()
        );
    }

    #region extractVariables
    public function testExtractVariablesFromPath_basic()
    {
        $instance = $this->getInstanceWithEmptyConfig();
        $columns = $innerJoins = [];
        $this->callMethod($instance, 'extractVariablesFromPath', ["{variable1}_{variable2}", &$columns, &$innerJoins]);

        $this->assertArrayHasKey('variable1',  $columns);
        $this->assertArrayHasKey('variable2',  $columns);
        $this->assertEmpty($innerJoins);
    }
    public function testExtractVariablesFromPath_withInnerJoins()
    {
        $instance = $this->getInstanceWithEmptyConfig();
        $columns = $innerJoins = [];
        $this->callMethod($instance, 'extractVariablesFromPath', ["{variable3.variable4}_{variable5}", &$columns, &$innerJoins]);

        $this->assertArrayHasKey('variable5',           $columns);
        $this->assertArrayHasKey('variable3.variable4', $columns);
        $this->assertContains('variable3', $innerJoins);
    }
    #endregion

    #region replaceVariables
    public function testReplaceVariablesFromPath_basic()
    {
        $columns = [
            'variable6' => '',
            'variable7' => ''
        ];
        $data = [
            'variable6' => 'datavalue6',
            'variable7' => 'datavalue7'
        ];

        $instance = $this->getInstanceWithEmptyConfig();
        $value = $this->callMethod($instance, 'replaceVariablesFromPath', ["{variable6}_{variable7}", $data, &$columns]);

        $this->assertEquals('datavalue6', $columns['variable6']);
        $this->assertEquals('datavalue7', $columns['variable7']);
        $this->assertEquals('datavalue6_datavalue7', $value);
    }
    public function testReplaceVariablesFromPath_withInnerJoins()
    {
        $columns = [
            'variable8.variable9'   => '',
            'variable10.variable11' => ''
        ];
        $data = [
            'variable8.variable9'   => 'datavalue9',
            'variable10.variable11' => 'datavalue11'
        ];

        $instance = $this->getInstanceWithEmptyConfig();
        $value = $this->callMethod($instance, 'replaceVariablesFromPath',["{variable8.variable9}_{variable10.variable11}", $data, &$columns]);

        $this->assertEquals('datavalue9',  $columns['variable8.variable9']);
        $this->assertEquals('datavalue11', $columns['variable10.variable11']);
        $this->assertEquals('datavalue9_datavalue11', $value);
    }
    #endregion
}

<?php

namespace DBCheckerTests\modules\FileCheck\FileCheck;

use DBChecker\modules\FileCheck\FileCheckModule;
use DBChecker\modules\ModuleManager;
use ReflectionClass;

class FileCheckTest extends \PHPUnit\Framework\TestCase
{
    private $instance;

    public function setUp()
    {
        parent::setUp();
        $module         = new FileCheckModule();
        $moduleManager = new ModuleManager();
        $moduleManager->loadModule($module, [$module->getName() => [
            'mapping' => [
                []
            ]
        ]]);
        $this->instance = $module->getWorker();
    }

    private function getMethod(string $method) : \ReflectionMethod
    {
        $reflector = new ReflectionClass(get_class($this->instance));
        $method = $reflector->getMethod($method);
        $method->setAccessible(true);
        return $method;
    }

    public function testExtractVariablesFromPath_basic()
    {
        $method  = $this->getMethod('extractVariablesFromPath');
        $columns = $innerJoins = [];
        $method->invokeArgs($this->instance, ["{variable1}_{variable2}", &$columns, &$innerJoins]);

        $this->assertArrayHasKey('variable1',  $columns);
        $this->assertArrayHasKey('variable2',  $columns);
        $this->assertEmpty($innerJoins);
    }
    public function testExtractVariablesFromPath_withInnerJoins()
    {
        $method  = $this->getMethod('extractVariablesFromPath');
        $columns = $innerJoins = [];
        $method->invokeArgs($this->instance, ["{variable3.variable4}_{variable5}", &$columns, &$innerJoins]);

        $this->assertArrayHasKey('variable5',           $columns);
        $this->assertArrayHasKey('variable3.variable4', $columns);
        $this->assertContains('variable3', $innerJoins);
    }

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

        $method  = $this->getMethod('replaceVariablesFromPath');
        $value = $method->invokeArgs($this->instance, ["{variable6}_{variable7}", $data, &$columns]);

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

        $method  = $this->getMethod('replaceVariablesFromPath');
        $value = $method->invokeArgs($this->instance, ["{variable8.variable9}_{variable10.variable11}", $data, &$columns]);

        $this->assertEquals('datavalue9',  $columns['variable8.variable9']);
        $this->assertEquals('datavalue11', $columns['variable10.variable11']);
        $this->assertEquals('datavalue9_datavalue11', $value);
    }
}

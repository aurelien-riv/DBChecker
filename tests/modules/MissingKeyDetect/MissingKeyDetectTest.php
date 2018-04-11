<?php

namespace DBCheckerTests\modules\MissingCompressionDetect;

use DBChecker\modules\MissingKeyDetect\MissingKeyDetect;
use DBChecker\modules\MissingKeyDetect\MissingKeyDetectMatch;
use DBChecker\modules\MissingKeyDetect\MissingKeyDetectModule;
use DBChecker\modules\ModuleManager;
use ReflectionClass;

final class MissingKeyDetectTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ModuleManager $module
     */
    private $moduleManager;

    public function setUp()
    {
        parent::setUp();
        $this->moduleManager = new ModuleManager();
    }

    #region split
    public function getInstanceWithEmptyConfig()
    {
        $module = new MissingKeyDetectModule();
        $this->moduleManager->loadModule($module, [$module->getName() => []]);
        return $this->moduleManager->getWorkers()->current();
    }
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

    private function getMethod($instance, string $method) : \ReflectionMethod
    {
        $reflector = new ReflectionClass(get_class($instance));
        $method = $reflector->getMethod($method);
        $method->setAccessible(true);
        return $method;
    }

    public function testRunWithPatterns()
    {

        $module = new MissingKeyDetectModule();
        $this->moduleManager->loadModule($module, [$module->getName() => [
            'patterns' => [
                '_id$'
            ]
        ]]);
        /** @var MissingKeyDetect $instance */
        $instance = $this->moduleManager->getWorkers()->current();
        $method = $this->getMethod($instance, 'runWithPatterns');
        $data = $method->invokeArgs($instance, ["", [
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

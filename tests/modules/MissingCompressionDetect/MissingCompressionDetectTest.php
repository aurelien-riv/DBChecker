<?php

namespace DBCheckerTests\modules\MissingCompressionDetect;

use DBChecker\modules\MissingCompressionDetect\CompressionUnsupportedMatch;
use DBChecker\modules\MissingCompressionDetect\DuplicateCompressionMatch;
use DBChecker\modules\MissingCompressionDetect\MissingCompressionDetect;
use DBChecker\modules\MissingCompressionDetect\MissingCompressionDetectModule;
use DBChecker\modules\MissingCompressionDetect\MissingCompressionMatch;

final class MissingCompressionDetectTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MissingCompressionDetect $instance
     */
    private $instance;

    public function setUp()
    {
        parent::setUp();
        $module = new MissingCompressionDetectModule();
        $module->loadConfig([]);
        $this->instance = $module->getWorker();
    }

    #region testYieldOnError
    public function testYieldOnError_0_0_0()
    {
        $this->assertNull($this->instance->yieldOnError("", "", false, false, false)->current());
    }
    public function testYieldOnError_0_0_1()
    {
        $this->assertNull($this->instance->yieldOnError("", "", false, false, true)->current());
    }
    public function testYieldOnError_0_1_0()
    {
        $this->assertInstanceOf(
            CompressionUnsupportedMatch::class,
            $this->instance->yieldOnError("", "", false, true, false)->current()
        );
    }
    public function testYieldOnError_0_1_1()
    {
        $this->assertInstanceOf(
            MissingCompressionMatch::class,
            $this->instance->yieldOnError("", "", false, true, true)->current()
        );
    }
    public function testYieldOnError_1_0_0()
    {
        $this->expectException(\LogicException::class);
        $this->instance->yieldOnError("", "", true, false, false)->current();
    }
    public function testYieldOnError_1_0_1()
    {
        $this->assertInstanceOf(
            DuplicateCompressionMatch::class,
            $this->instance->yieldOnError("", "", true, false, true)->current()
        );
    }
    public function testYieldOnError_1_1_0()
    {
        $this->expectException(\LogicException::class);
        $this->instance->yieldOnError("", "", true, true, false)->current();
    }
    public function testYieldOnError_1_1_1()
    {
        $this->assertNull($this->instance->yieldOnError("", "", true, true, true)->current());
    }
    #endregion
}

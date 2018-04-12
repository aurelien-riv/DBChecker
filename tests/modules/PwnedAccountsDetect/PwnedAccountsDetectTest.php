<?php

namespace DBCheckerTests\modules\PwnedAccountsDetect;

use DBChecker\modules\ModuleManager;
use DBChecker\modules\PwnedAccountsDetect\PwnedAccountDetectMatch;
use DBChecker\modules\PwnedAccountsDetect\PwnedAccountsDetect;
use DBChecker\modules\PwnedAccountsDetect\PwnedAccountsDetectModule;
use DBCheckerTests\BypassVisibilityTrait;

class PwnedAccountsDetectTest extends \PHPUnit\Framework\TestCase
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

    public function getInstance() : PwnedAccountsDetect
    {
        $module = new PwnedAccountsDetectModule();
        $this->moduleManager->loadModule($module, [$module->getName() => [
            'mapping' => [
                ['table' => '', 'login_column' => '']
            ]
        ]]);
        return $this->moduleManager->getWorkers()->current();
    }

    public function testcheckLogin_false()
    {
        $instance = $this->getInstance();
        $this->assertNull(
            $this->callMethod($instance, 'checkLogin', ["this_does_not_exist@something.fr", '', '', ''])->current()
        );
    }

    public function testcheckLogin_true()
    {
        $instance = $this->getInstance();
        $this->assertInstanceOf(
            PwnedAccountDetectMatch::class,
            $this->callMethod($instance, 'checkLogin', ["test@example.com", '', '', ''])->current()
        );
    }
}
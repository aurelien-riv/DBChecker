<?php

namespace DBChecker\modules;

use DBChecker\BaseModuleInterface;
use DBChecker\ModuleInterface;
use DBChecker\modules\AnalyzeTableCheck\AnalyzeTableCheckModule;
use DBChecker\modules\DataBase\DatabasesModule;
use DBChecker\modules\DataIntegrityCheck\DataIntegrityCheckModule;
use DBChecker\modules\FileCheck\FileCheckModule;
use DBChecker\modules\FragmentationCheck\FragmentationCheckModule;
use DBChecker\modules\MissingCompressionDetect\MissingCompressionDetectModule;
use DBChecker\modules\MissingKeyDetect\MissingKeyDetectModule;
use DBChecker\modules\PwnedAccountsDetect\PwnedAccountsDetectModule;
use DBChecker\modules\RelCheck\RelCheckModule;
use DBChecker\modules\SchemaIntegrityCheck\SchemaIntegrityCheckModule;
use DBChecker\modules\UniqueIntegrityCheck\UniqueIntegrityCheckModule;
use Symfony\Component\Config\Definition\Processor;

class ModuleManager
{
    private $modules = [];

    const ENABLED_MODULES = [
        DatabasesModule::class,
        RelCheckModule::class,
        UniqueIntegrityCheckModule::class,
        FileCheckModule::class,
        MissingKeyDetectModule::class,
        SchemaIntegrityCheckModule::class,
        DataIntegrityCheckModule::class,
        MissingCompressionDetectModule::class,
        FragmentationCheckModule::class,
        AnalyzeTableCheckModule::class,
        PwnedAccountsDetectModule::class
    ];

    public function loadModule(BaseModuleInterface $module, $settings)
    {
        $moduleName = $module->getName();

        if (array_key_exists($moduleName, $settings))
        {
            $processor      = new Processor();
            $tree           = $module->getConfigTreeBuilder()->buildTree();
            $moduleSettings = $processor->process($tree, [$moduleName => $settings[$moduleName]]);

            $module->loadConfig($moduleSettings);
            $this->modules[] = $module;
        }
    }

    public function getDatabaseModule() : DatabasesModule
    {
        foreach ($this->modules as $module)
        {
            if ($module instanceof DatabasesModule)
            {
                return $module;
            }
        }
        return null;
    }

    public function getWorkers()
    {
        foreach ($this->modules as $module)
        {
            if ($module instanceof ModuleInterface)
            {
                yield $module->getWorker();
            }
        }
    }
}
<?php

namespace DBChecker\modules;

use DBChecker\BaseModuleInterface;
use DBChecker\InputModules\InputModuleManager;
use DBChecker\ModuleInterface;
use DBChecker\modules\AnalyzeTableCheck\AnalyzeTableCheckModule;
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
        InputModuleManager::class,
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

    public function loadModules($settings)
    {
        foreach (static::ENABLED_MODULES as $module)
        {
            $this->loadModule(new $module(), $settings);
        }
    }

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
        else if ($module instanceof InputModuleManager)
        {
            throw new \InvalidArgumentException("No input module has been defined");
        }
    }

    public function getDatabaseModule() : InputModuleManager
    {
        foreach ($this->modules as $module)
        {
            if ($module instanceof InputModuleManager)
            {
                return $module;
            }
        }
        throw new \LogicException("This code shouldn't be reached");
    }

    public function getWorkers() : \Generator
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
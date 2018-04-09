<?php

namespace DBChecker;

use DBChecker\modules\DataIntegrityCheck\DataIntegrityCheckModule;
use DBChecker\modules\FileCheck\FileCheckModule;
use DBChecker\modules\MissingKeyDetect\MissingKeyDetectModule;
use DBChecker\modules\RelCheck\RelCheckModule;
use DBChecker\modules\SchemaIntegrityCheck\SchemaIntegrityCheckModule;
use DBChecker\modules\UniqueIntegrityCheck\UniqueIntegrityCheckModule;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml;

class Config
{
    private $moduleWorkers = [];

    /**
     * @var DatabasesModule $databases
     */
    private $databases;

    public function __construct($yamlPath)
    {
        $settings = Yaml::parseFile($yamlPath);

        $this->databases = new DatabasesModule();
        $this->loadModule($this->databases, $settings);

        foreach ($this->getModuleClasses() as $module)
        {
            $this->loadModule(new $module($this), $settings);
        }
    }

    private function getModuleClasses()
    {
        return [
            RelCheckModule::class,
            UniqueIntegrityCheckModule::class,
            FileCheckModule::class,
            MissingKeyDetectModule::class,
            SchemaIntegrityCheckModule::class,
            DataIntegrityCheckModule::class
        ];
    }

    protected function loadModule(BaseModuleInterface $module, $settings)
    {
        $moduleName = $module->getName();

        if (array_key_exists($moduleName, $settings))
        {
            $processor      = new Processor();
            $tree           = $module->getConfigTreeBuilder()->buildTree();
            $moduleSettings = $processor->process($tree, [$moduleName => $settings[$moduleName]]);

            $module->loadConfig($moduleSettings);
            if ($module instanceof ModuleInterface)
            {
                $this->moduleWorkers[] = $module->getWorker();
            }
        }
    }

    /**
     * @return ModuleWorkerInterface[]
     */
    public function getModuleWorkers()
    {
        return $this->moduleWorkers;
    }

    public function getQueries()
    {
        return $this->databases->getConnections();
    }
}
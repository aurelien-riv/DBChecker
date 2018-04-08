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
    private $filecheck            = [];
    private $dataintegritycheck   = [];
    private $schemaintegritycheck = [];
    private $missingkeydetect     = [];

    private $moduleWorkers = [];

    /**
     * @var DatabasesModule $databases
     */
    private $databases;

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
        $tree = $module->getConfigTreeBuilder()->buildTree();

        $processor = new Processor();
        if (array_key_exists($moduleName, $settings))
        {
            $moduleSettings = $processor->process($tree, [$moduleName => $settings[$moduleName]]);

            if ($module instanceof ModuleInterface)
            {
                // FIXME should hold their configuration themselves
                $this->{$moduleName} = $module->loadConfig($moduleSettings);
                $this->moduleWorkers[] = $module->getWorker();
            }
            else
            {
                $module->loadConfig($moduleSettings);
            }
        }
    }

    protected function parseYaml($yamlPath)
    {
        $settings = Yaml::parseFile($yamlPath);

        $this->databases = new DatabasesModule();
        $this->loadModule($this->databases, $settings);

        foreach ($this->getModuleClasses() as $module)
        {
            $this->loadModule(new $module($this), $settings);
        }
    }

    public function __construct($yamlPath)
    {
        $this->parseYaml($yamlPath);
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

    /**
     * @deprecated
     * @return array
     */
    public function getFilecheck()
    {
        return $this->filecheck;
    }

    /**
     * @deprecated
     * @return array
     */
    public function getDataintegrity()
    {
        return $this->dataintegritycheck;
    }

    /**
     * @deprecated
     * @return array
     */
    public function getSchemaIntegrity()
    {
        return $this->schemaintegritycheck;
    }

    /**
     * @deprecated
     * @return array
     */
    public function getMissingKey()
    {
        return $this->missingkeydetect;
    }
}
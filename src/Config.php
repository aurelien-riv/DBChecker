<?php

namespace DBChecker;

use DBChecker\modules\ModuleManager;
use Symfony\Component\Yaml\Yaml;

class Config
{
    private $moduleManager;

    public function __construct($yamlPath)
    {
        $this->moduleManager = new ModuleManager();
        $this->moduleManager->loadModules(Yaml::parseFile($yamlPath));
    }

    /**
     * @return \Generator
     */
    public function getModuleWorkers()
    {
        yield from $this->moduleManager->getWorkers();
    }

    public function getDBALs() : \Generator
    {
        yield from $this->moduleManager->getDatabaseModule()->getDBALs();
    }
}
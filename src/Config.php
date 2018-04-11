<?php

namespace DBChecker;

use DBChecker\modules\ModuleManager;
use Symfony\Component\Yaml\Yaml;

class Config
{
    private $moduleManager;


    public function __construct($yamlPath)
    {
        $settings = Yaml::parseFile($yamlPath);

        $this->moduleManager = new ModuleManager();
        foreach (ModuleManager::ENABLED_MODULES as $module)
        {
            $this->moduleManager->loadModule(new $module(), $settings);
        }
    }

    /**
     * @return \Generator
     */
    public function getModuleWorkers()
    {
        yield from $this->moduleManager->getWorkers();
    }

    public function getQueries()
    {
        return $this->moduleManager->getDatabaseModule()->getConnections();
    }
}
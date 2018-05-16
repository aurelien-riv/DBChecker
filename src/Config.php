<?php

namespace DBChecker;

use DBChecker\InputModules\AbstractDBAL;
use DBChecker\InputModules\InputModuleManager;
use DBChecker\modules\DataBase\DatabasesModule;
use DBChecker\modules\ModuleManager;
use Symfony\Component\Yaml\Yaml;

class Config
{
    private $moduleManager;

    public function __construct($yamlPath)
    {
        $settings = Yaml::parseFile($yamlPath);

        $this->moduleManager = new ModuleManager();

        $this->moduleManager->loadModule(new InputModuleManager(), $settings);
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

    public function getDBALs() : \Generator
    {
        return $this->moduleManager->getDatabaseModule()->getDBALs();
    }
}
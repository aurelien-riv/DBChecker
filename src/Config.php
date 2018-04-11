<?php

namespace DBChecker;

use DBChecker\modules\DataBase\DatabasesModule;
use DBChecker\modules\ModuleManager;
use Symfony\Component\Yaml\Yaml;

class Config
{
    private $moduleManager;

    /**
     * @var DatabasesModule $databases
     */
    private $databases;

    public function __construct($yamlPath)
    {
        $settings = Yaml::parseFile($yamlPath);

        $this->databases     = new DatabasesModule();
        $this->moduleManager = new ModuleManager();
        $this->moduleManager->loadModule($this->databases, $settings);

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
        return $this->databases->getConnections();
    }
}
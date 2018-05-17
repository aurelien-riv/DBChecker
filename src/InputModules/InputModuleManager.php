<?php

namespace DBChecker\InputModules;

use DBChecker\BaseModuleInterface;
use DBChecker\InputModules\MySQL\MySQLModule;
use DBChecker\InputModules\SQLite\SQLiteModule;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class InputModuleManager implements BaseModuleInterface
{
    private $modules = [];

    public function getName()
    {
        return 'databases';
    }

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $treeBuilder->root($this->getName())
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('connections')
                ->isRequired()
                ->requiresAtLeastOneElement()
                ->arrayPrototype()
                    ->ignoreExtraKeys(false)
                    ->children()
                        ->enumNode('engine')
                            ->values(['mysql', 'sqlite'])
                            ->isRequired()
                        ->end()
                    ->end()
                ->end()
            ->end();
        return $treeBuilder;
    }

    private function findEngineModule($engineName) : BaseModuleInterface
    {
        foreach ([MySQLModule::class, SQLiteModule::class] as $engineModule)
        {
            /** @var BaseModuleInterface $module */
            $module = new $engineModule();
            if ($module->getName() === $engineName)
            {
                return $module;
            }
        }
        throw new \InvalidArgumentException("Unknown module $engineName");
    }

    public function loadConfig(array $config)
    {
        foreach ($config['connections'] as $cnx)
        {
            $module = $this->findEngineModule($cnx['engine']);
            unset($cnx['engine']);
            $module->loadConfig($cnx);
            $this->modules[] = $module;
        }
    }

    public function getDBALs()
    {
        foreach ($this->modules as $module)
        {
            yield $module->getDbal();
        }
    }
}
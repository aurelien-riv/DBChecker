<?php

namespace DBChecker\modules\SchemaIntegrityCheck;

use DBChecker\Config;
use DBChecker\ModuleInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class SchemaIntegrityCheckModule implements ModuleInterface
{
    protected $config;

    public function getName()
    {
        return 'schemaintegritycheck';
    }

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $treeBuilder->root($this->getName())
            ->children()
                ->booleanNode('allow_extras')
                    ->defaultFalse()
                ->end()
                ->arrayNode('ignore')
                    ->info('Ignore extra tables that matches one of the regexp if allow_extras if false')
                    ->scalarPrototype()
                ->end()
                ->arrayNode('mapping')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->arrayPrototype()
                        ->useAttributeAsKey('key')
                        ->prototype('scalar')
                            ->info('A SHA1 sum')
                        ->end()
                    ->end()
                ->end()
            ->end();
        return $treeBuilder;
    }

    public function loadConfig(array $config)
    {
        $this->config = $config;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function getWorker()
    {
        return new SchemaIntegrityCheck($this);
    }
}
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

    private function addMapping()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('mapping');

        return $node
            ->isRequired()
            ->cannotBeEmpty()
            ->arrayPrototype()
                ->useAttributeAsKey('key')
                ->scalarPrototype()
                    ->info('A SHA1 sum')
                ->end()
            ->end();
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
                    ->scalarPrototype()->end()
                ->end()
                ->append($this->addMapping())
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
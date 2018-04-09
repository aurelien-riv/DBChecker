<?php

namespace DBChecker\modules\DataIntegrityCheck;

use DBChecker\Config;
use DBChecker\ModuleInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class DataIntegrityCheckModule implements ModuleInterface
{
    protected $config;

    public function getName()
    {
        return 'datainregritycheck';
    }

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $treeBuilder->root($this->getName())
            ->children()
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
        return new DataIntegrityCheck($this);
    }
}
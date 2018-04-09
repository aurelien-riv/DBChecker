<?php

namespace DBChecker\modules\MissingKeyDetect;

use DBChecker\Config;
use DBChecker\ModuleInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class MissingKeyDetectModule implements ModuleInterface
{
    protected $config;

    public function getName()
    {
        return 'missingkeydetect';
    }

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $treeBuilder->root($this->getName())
            ->children()
                ->arrayNode('patterns')
                    ->scalarPrototype()->end()
                ->end()
                ->floatNode('threshold')
                    ->defaultValue(30)
                    ->min(0)
                    ->max(100)
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
        return new MissingKeyDetect($this);
    }
}
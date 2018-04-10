<?php

namespace DBChecker\modules\MissingCompressionDetect;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class MissingCompressionDetectModule implements \DBChecker\ModuleInterface
{
    private $config;

    public function getName()
    {
        return 'missingcompressiondetect';
    }

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $treeBuilder->root($this->getName())
            ->children()
                ->integerNode('largeTableSize')
                    ->info('Tables below this size in MB will be ignored')
                    ->defaultValue(100)
                ->end()
                ->integerNode('minimalCompressionRatio')
                    ->defaultValue(80)
                    ->info('Data is considered compressible if the gain is lower than the original size * minimalCompressionRatio')
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
        return new MissingCompressionDetect($this);
    }
}
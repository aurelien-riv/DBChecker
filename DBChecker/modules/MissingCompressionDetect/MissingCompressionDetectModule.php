<?php

namespace DBChecker\modules\MissingCompressionDetect;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class MissingCompressionDetectModule implements \DBChecker\ModuleInterface
{
    public function getName()
    {
        return 'missingcompressiondetect';
    }

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $treeBuilder->root($this->getName());
        return $treeBuilder;
    }

    public function loadConfig(array $config)
    {
    }

    public function getConfig()
    {
        return [];
    }

    public function getWorker()
    {
        return new MissingCompressionDetect();
    }
}
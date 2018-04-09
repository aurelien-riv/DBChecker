<?php

namespace DBChecker\modules\RelCheck;

use DBChecker\Config;
use DBChecker\ModuleInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class RelCheckModule implements ModuleInterface
{
    public function getName()
    {
        return 'relcheck';
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
        return new RelCheck();
    }
}
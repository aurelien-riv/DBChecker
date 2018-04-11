<?php

namespace DBChecker\modules\UniqueIntegrityCheck;

use DBChecker\Config;
use DBChecker\ModuleInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class UniqueIntegrityCheckModule implements ModuleInterface
{
    public function getName()
    {
        return 'uniqueintegritycheck';
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
        return new UniqueIntegrityCheck();
    }
}
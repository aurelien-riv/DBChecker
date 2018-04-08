<?php

namespace DBChecker\modules\UniqueIntegrityCheck;

use DBChecker\Config;
use DBChecker\ModuleInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class UniqueIntegrityCheckModule implements ModuleInterface
{
    protected $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function getName()
    {
        return 'dataintegrity';
    }

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $treeBuilder->root($this->getName());
        return $treeBuilder;
    }

    public function loadConfig(array $config)
    {
        return $config;
    }

    public function getWorker()
    {
        return new UniqueIntegrityCheck($this->config);
    }
}
<?php

namespace DBChecker\modules\DataIntegrityCheck;

use DBChecker\Config;
use DBChecker\ModuleInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class DataIntegrityCheckModule implements ModuleInterface
{
    protected $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function getName()
    {
        return 'datainregrity';
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
        return new DataIntegrityCheck($this->config);
    }
}
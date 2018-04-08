<?php

namespace DBChecker\modules\RelCheck;

use DBChecker\Config;
use DBChecker\ModuleInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class RelCheckModule implements ModuleInterface
{
    protected $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

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
        return $config;
    }

    public function getWorker()
    {
        return new RelCheck($this->config);
    }
}
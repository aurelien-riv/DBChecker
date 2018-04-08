<?php

namespace DBChecker;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;

interface ModuleInterface
{
    /**
     * @return string
     *
     * The name of the module, used as the root of the configtree
     */
    public function getName();

    /**
     * @return TreeBuilder
     *
     * The configuration tree
     */
    public function getConfigTreeBuilder();

    /**
     * @param array $config
     *
     * Load the settings of the module
     */
    public function loadConfig(array $config);

    /**
     * @return ModuleWorkerInterface
     */
    public function getWorker();
}
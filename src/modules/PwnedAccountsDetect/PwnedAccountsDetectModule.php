<?php

namespace DBChecker\modules\PwnedAccountsDetect;

use DBChecker\ModuleInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class PwnedAccountsDetectModule implements ModuleInterface
{
    protected $config;

    public function getName()
    {
        return 'pwnedaccountsdetect';
    }

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $treeBuilder->root($this->getName())
            ->children()
            ->booleanNode('show_new_only')->defaultTrue()->end()
            ->arrayNode('mapping')
                ->isRequired()
                ->requiresAtLeastOneElement()
                ->arrayPrototype()
                    ->children()
                        ->scalarNode('table')->isRequired()->end()
                        ->scalarNode('login_column')
                            ->isRequired()
                            ->info('Either an email address (prefered) or a login')
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
        return new PwnedAccountsDetect($this);
    }
}
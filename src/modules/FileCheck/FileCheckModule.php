<?php

namespace DBChecker\modules\FileCheck;

use DBChecker\ModuleInterface;
use DBChecker\ModuleWorkerInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class FileCheckModule implements ModuleInterface
{
    protected $config;

    public function getName()
    {
        return 'filecheck';
    }

    private function addSSHNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('ssh');

        return $node
            ->info('Checks path using SFTP instead of local is_file')
            ->children()
                ->scalarNode('host')->end()
                ->integerNode('port')->defaultValue(22)->end()
                ->scalarNode('user')->end()
                ->scalarNode('password')->defaultNull()->end()
                ->scalarNode('pkey_file')->defaultNull()->end()
                ->scalarNode('pkey_passphrase')
                    ->defaultNull()
                    ->info('Passphrase for the private key, or "prompt" for interactive')
                ->end()
            ->end();
    }

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $treeBuilder->root($this->getName())
            ->children()
                ->booleanNode('enable_remotes')
                    ->defaultFalse()
                    ->info("If true, http and https URL will be fetched to detect 4xx and 5xx errors")
                ->end()
                ->append($this->addSSHNode())
                ->arrayNode('mapping')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->arrayPrototype()
                        ->useAttributeAsKey('key')
                        ->prototype('scalar')->end()
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

    /**
     * @return FileCheck|ModuleWorkerInterface
     * @throws \Exception
     */
    public function getWorker()
    {
        return new FileCheck($this);
    }
}
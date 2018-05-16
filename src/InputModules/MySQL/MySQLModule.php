<?php

namespace DBChecker\InputModules\MySQL;

use DBChecker\InputModules\AbstractDBAL;
use DBChecker\InputModules\InputModuleInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class MySQLModule implements InputModuleInterface
{
    private $dbal;

    public function getName()
    {
        return "mysql";
    }

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $treeBuilder->root($this->getName())
            ->children()
                ->scalarNode('name')
                    ->info('A name to use instead of the db name in the output')
                    ->defaultNull()
                ->end()
                ->scalarNode('db')->info('The database name')->end()
                ->scalarNode('login')->defaultNull()->end()
                ->scalarNode('password')->defaultNull()->end()
                ->scalarNode('host')->defaultValue('localhost')->end()
                ->integerNode('port')->end()
            ->end();
        return $treeBuilder;
    }

    public function loadConfig(array $config)
    {
        $dsn = "mysql:dbname={$config['db']};host={$config['host']};port={$config['port']}";
        $pdo = new \PDO($dsn, $config['login'], $config['password']);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $queries = new MySQLQueries($pdo, $config['name'] ?? $config['db']);
        $this->dbal = new MySQLDBAL($queries);
    }

    public function getDbal() : AbstractDBAL
    {
        return $this->dbal;
    }
}
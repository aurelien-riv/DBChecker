<?php

namespace DBChecker\InputModules\SQLite;

use DBChecker\InputModules\AbstractDBAL;
use DBChecker\InputModules\InputModuleInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class SQLiteModule implements InputModuleInterface
{
    private $dbal;

    public function getName()
    {
        return "sqlite";
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
            ->end();
        return $treeBuilder;
    }

    public function loadConfig(array $config)
    {
        $pdo = new \PDO("sqlite:{$config['db']}");
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $queries = new SQLiteQueries($pdo, $config['name'] ?? $config['db']);
        $this->dbal = new SQLiteDBAL($queries);
    }

    public function getDbal() : AbstractDBAL
    {
        return $this->dbal;
    }
}
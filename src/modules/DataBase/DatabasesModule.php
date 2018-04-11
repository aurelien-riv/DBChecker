<?php

namespace DBChecker\modules\DataBase;

use DBChecker\BaseModuleInterface;
use DBChecker\DBQueries\AbstractDbQueries;
use DBChecker\DBQueries\MySQLQueries;
use DBChecker\DBQueries\SQLiteQueries;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class DatabasesModule implements BaseModuleInterface
{
    private $connections = [];

    public function getName()
    {
        return 'databases';
    }

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $treeBuilder->root($this->getName())
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('connections')
                ->requiresAtLeastOneElement()
                ->arrayPrototype()
                    ->children()
                        ->scalarNode('name')
                            ->info('A name to use instead of the db name in the output')
                            ->defaultNull()
                        ->end()
                        ->enumNode('engine')
                            ->values(['mysql', 'sqlite'])
                            ->isRequired()
                        ->end()
                        ->scalarNode('dsn')->end()
                        ->scalarNode('db')->info('The database name')->end()
                        ->scalarNode('login')->defaultNull()->end()
                        ->scalarNode('password')->defaultNull()->end()
                        ->scalarNode('host')->defaultValue('localhost')->end()
                        ->integerNode('port')->end()
                    ->end()
                ->end()
            ->end();
        return $treeBuilder;
    }

    /**
     * @return AbstractDbQueries[]
     */
    public function getConnections()
    {
        return $this->connections;
    }

    public function addConnection($cnx)
    {
        $dsn = $cnx['dsn'] ?? "{$cnx['engine']}:dbname={$cnx['db']};host={$cnx['host']};port={$cnx['port']}";
        $pdo = new \PDO($dsn, $cnx['login'], $cnx['password']);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        if ($cnx['engine'] == 'mysql')
        {
            $this->connections[] = new MySQLQueries($pdo, $cnx['name'] ?? $cnx['db']);
        }
        else if ($cnx['engine'] === 'sqlite')
        {
            $this->connections[] = new SQLiteQueries($pdo, $cnx['name'] ?? $cnx['db']);
        }
    }

    public function loadConfig(array $config)
    {
        foreach ($config['connections'] as $cnx)
        {
            $this->addConnection($cnx);
        }
    }

    public function getConfig()
    {
        return [];
    }
}
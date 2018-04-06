<?php

namespace DBChecker;

use DBChecker\DBQueries\MySQLQueries;
use Symfony\Component\Yaml\Yaml;

require_once('DBQueries/MySQLQueries.php');

class Config
{
    private $db;
    private $login;
    private $password;
    private $engine;
    private $host;
    private $port;
    private $filecheck     = [];
    private $dataintegrity = [];
    private $schemaintegrity = [];
    private $schemaintegrity_config = [];

    private $pdo = null;

    protected function parseYaml($yamlPath)
    {
        $settings = Yaml::parseFile($yamlPath);

        $dbsettings     = $settings['database'];
        $this->db       = $dbsettings['db'];
        $this->login    = $dbsettings['login'];
        $this->password = $dbsettings['password'];
        $this->engine   = $dbsettings['engine'];
        $this->host     = $dbsettings['host'];
        $this->port     = $dbsettings['port'];

        // FIXME move to the modules themselves
        if (isset($settings['filecheck']))
        {
            $this->filecheck['settings']['enable_remotes'] = false;
            if (isset($settings['filecheck']['settings']['enable_remotes']))
            {
                $this->filecheck['settings']['enable_remotes'] = $settings['filecheck']['settings']['enable_remotes'];
            }
            foreach ($settings['filecheck']['mapping'] as $item)
            {
                $this->filecheck['mapping'][] = [
                    'table'  => key($item),
                    'path'   => $item[key($item)]
                ];
            }
        }

        if (isset($settings['dataintegrity']))
        {
            foreach ($settings['dataintegrity'] as $item)
            {
                $this->dataintegrity[key($item)] = $item[key($item)];
            }
        }

        if (isset($settings['schemaintegrity']))
        {
            $this->schemaintegrity['settings']['allow_extras'] = false;
            if (isset($settings['schemaintegrity']['settings']['allow_extras']))
            {
                $this->schemaintegrity['settings']['allow_extras'] = $settings['schemaintegrity']['settings']['allow_extras'];
            }

            $this->schemaintegrity['settings']['ignore'] = [];
            if (isset($settings['schemaintegrity']['settings']['ignore']))
            {
                foreach ($settings['schemaintegrity']['settings']['ignore'] as $ignore)
                {
                    $this->schemaintegrity['settings']['ignore'][] = $ignore;
                }
            }

            foreach ($settings['schemaintegrity']['mapping'] as $item)
            {
                $this->schemaintegrity['mapping'][key($item)] = $item[key($item)];
            }
        }
    }

    public function __construct($yamlPath)
    {
        $this->parseYaml($yamlPath);

        $this->pdo = new \PDO($this->getDsn(), $this->login, $this->password);
    }

    public function getFilecheck()
    {
        return $this->filecheck;
    }

    public function getDataintegrity()
    {
        return $this->dataintegrity;
    }

    public function getSchemaIntegrity()
    {
        return $this->schemaintegrity;
    }

    public function getSchemaintegrityConfig()
    {
        return $this->schemaintegrity_config;
    }

    public function getDsn()
    {
        return "{$this->engine}:dbname={$this->db};host={$this->host};port={$this->port}";
    }

    /**
     * @return null|\PDO
     */
    public function getPdo()
    {
        return $this->pdo;
    }

    public function getQueries()
    {
        switch ($this->engine)
        {
            case 'mysql': return new MySQLQueries($this->pdo);
            default: throw new \InvalidArgumentException("Unsupported engine {$this->engine}");
        }
    }
}
<?php

namespace DBChecker;

use DBChecker\DBQueries\MySQLQueries;

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

    public function __construct($iniPath='')
    {
        if (empty($iniPath))
        {
            $iniPath = __DIR__.DIRECTORY_SEPARATOR."config.ini";
        }
        $settings = parse_ini_file($iniPath, true);
        $dbsettings = $settings['database'];

        $this->db       = $dbsettings['db'];
        $this->login    = $dbsettings['login'];
        $this->password = $dbsettings['password'];
        $this->engine   = $dbsettings['engine'];
        $this->host     = $dbsettings['host'];
        $this->port     = $dbsettings['port'];

        if (isset($settings['filecheck']))
        {
            foreach ($settings['filecheck'] as $k => $v)
            {
                // the second part of $k is unused and optional,  use it to
                // perform several checks on a table
                $this->filecheck[$k] = [
                    'table'  => explode('.', $k)[0],
                    'path'   => $v
                ];
            }
        }
        if (isset($settings['dataintegrity']))
        {
            foreach ($settings['dataintegrity'] as $table => $checksum)
                $this->dataintegrity[$table] = $checksum;
        }
        if (isset($settings['schemaintegrity']))
        {
            foreach ($settings['schemaintegrity'] as $table => $checksum)
                $this->schemaintegrity[$table] = $checksum;
            foreach ($settings['schemaintegrity_config'] as $option => $value)
                $this->schemaintegrity_config[$option] = $value;
        }

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
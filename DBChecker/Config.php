<?php

namespace DBChecker;

use DBChecker\DBQueries\MySQLQueries;

require_once('DBQueries/MySQLQueries.php');

class Config
{
    private $db            = '';
    private $login         = '';
    private $password      = '';
    private $engine        = '';
    private $host          = '';
    private $port          = '';
    private $filecheck     = [];
    private $dataintegrity = [];

    private $pdo = null;

    public function __construct()
    {
        $settings = parse_ini_file(__DIR__.DIRECTORY_SEPARATOR."config.ini", true);
        $dbsettings = $settings['database'];

        $this->db       = $dbsettings['db'];
        $this->login    = $dbsettings['login'];
        $this->password = $dbsettings['password'];
        $this->engine   = $dbsettings['engine'];
        $this->host     = $dbsettings['host'];
        $this->port     = $dbsettings['port'];

        foreach ($settings['filecheck'] as $k => $v)
        {
            $this->filecheck[$k] = [
                'table'  => explode('.', $k)[0],
                'column' => explode('.', $k)[1],
                'path'   => $v
            ];
        }
        foreach ($settings['dataintegrity'] as $table => $checksum)
        {
            $this->dataintegrity[$table] = $checksum;
        }

        $this->pdo = new \PDO($this->getDsn(), $this->login, $this->password);
    }

    public function getFilecheckConfig()
    {
        return $this->filecheck;
    }

    public function getDataintegrityConfig()
    {
        return $this->dataintegrity;
    }

    public function getDsn()
    {
        return "{$this->engine}:dbname={$this->db};host={$this->host};port={$this->port}";
    }
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
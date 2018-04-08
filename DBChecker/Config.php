<?php

namespace DBChecker;

use DBChecker\DBQueries\MySQLQueries;
use DBChecker\modules\DataIntegrityCheck\DataIntegrityCheckModule;
use DBChecker\modules\FileCheck\FileCheckModule;
use DBChecker\modules\MissingKeyDetect\MissingKeyDetectModule;
use DBChecker\modules\RelCheck\RelCheckModule;
use DBChecker\modules\SchemaIntegrityCheck\SchemaIntegrityCheckModule;
use DBChecker\modules\UniqueIntegrityCheck\UniqueIntegrityCheckModule;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml;

class Config
{
    private $db;
    private $login;
    private $password;
    private $engine;
    private $host;
    private $port;

    private $filecheck       = [];
    private $dataintegrity   = [];
    private $schemaintegrity = [];
    private $missingkey      = [];

    private $pdo = null;

    private $moduleWorkers = [];

    private function getModuleClasses()
    {
        return [
            RelCheckModule::class,
            UniqueIntegrityCheckModule::class,
            FileCheckModule::class,
            MissingKeyDetectModule::class,
            SchemaIntegrityCheckModule::class,
            DataIntegrityCheckModule::class
        ];
    }

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

        foreach ($this->getModuleClasses() as $module)
        {
            /** @var ModuleInterface $moduleInstance */
            $moduleInstance = new $module($this);
            $moduleName = $moduleInstance->getName();
            $tree = $moduleInstance->getConfigTreeBuilder()->buildTree();

            $processor = new Processor();
            if (isset($settings[$moduleName]))
            {
                $moduleSettings = $processor->process($tree, [$moduleName => $settings[$moduleName]]);

                // FIXME should hold their configuration themselves
                $this->{$moduleName} = $moduleInstance->loadConfig($moduleSettings);

                $this->moduleWorkers[] = $moduleInstance->getWorker();
            }
        }
    }

    public function __construct($yamlPath)
    {
        $this->parseYaml($yamlPath);

        $this->pdo = new \PDO($this->getDsn(), $this->login, $this->password);
    }

    /**
     * @return ModuleWorkerInterface[]
     */
    public function getModuleWorkers()
    {
        return $this->moduleWorkers;
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

    public function getMissingKey()
    {
        return $this->missingkey;
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
<?php

namespace DBChecker;

require_once('Config.php');
require_once('modules/FileCheck.php');
require_once('modules/RelCheck.php');
require_once('modules/DataIntegrityCheck.php');
require_once('modules/SchemaIntegrityCheck.php');
require_once('modules/UniqueIntegrity.php');
require_once('modules/MissingKeyDetect.php');

class DBChecker
{
    private $config;

    public function __construct($yamlPath)
    {
        $this->config = new Config($yamlPath);
    }

    public function run()
    {
        $check = new RelCheck($this->config);
        foreach ($check->run() as $msg)
            yield $msg;

        $check = new FileCheck($this->config);
        foreach ($check->run() as $msg)
            yield $msg;

        $check = new DataIntegrityCheck($this->config);
        foreach ($check->run() as $msg)
            yield $msg;

        $check = new SchemaIntegrityCheck($this->config);
        foreach ($check->run() as $msg)
            yield $msg;

        $check = new UniqueIntegrity($this->config);
        foreach ($check->run() as $msg)
            yield $msg;

        $check = new MissingKeyDetect($this->config);
        foreach ($check->run() as $msg)
            yield $msg;
    }
}

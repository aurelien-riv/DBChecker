<?php

namespace DBChecker;

require_once('Config.php');
require_once('modules/FileCheck.php');
require_once('modules/RelCheck.php');
require_once('modules/DataIntegrityCheck.php');

class DBChecker
{
    private $config;

    public function __construct()
    {
        $this->config = new Config();
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
    }
}
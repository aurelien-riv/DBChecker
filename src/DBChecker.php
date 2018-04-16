<?php

namespace DBChecker;

class DBChecker
{
    private $config;

    public function __construct($yamlPath)
    {
        $this->config = new Config($yamlPath);
    }

    public function run()
    {
        foreach ($this->config->getDBALs() as $dbal)
        {
            foreach ($this->config->getModuleWorkers() as $moduleWorker)
            {
                foreach ($moduleWorker->run($dbal) as $msg)
                {
                    yield $msg;
                }
            }
        }
    }
}

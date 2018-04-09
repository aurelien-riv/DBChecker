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
        foreach ($this->config->getQueries() as $queries)
        {
            foreach ($this->config->getModuleWorkers() as $moduleWorker)
            {
                foreach ($moduleWorker->run($queries) as $msg)
                {
                    yield $msg;
                }
            }
        }
    }
}

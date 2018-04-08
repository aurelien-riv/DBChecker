<?php

namespace DBChecker;

use DBChecker\DBQueries\AbstractDbQueries;

interface ModuleWorkerInterface
{
    /**
     * @param AbstractDbQueries $dbQueries
     * @return \Generator|array
     */
    public function run(AbstractDbQueries $dbQueries);
}
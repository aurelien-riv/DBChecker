<?php

namespace DBChecker;

use DBChecker\DBAL\AbstractDBAL;

interface ModuleWorkerInterface
{
    /**
     * @param AbstractDBAL $dbal
     * @return \Generator|array
     */
    public function run(AbstractDBAL $dbal);
}
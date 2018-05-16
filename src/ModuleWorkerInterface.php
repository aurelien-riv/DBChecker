<?php

namespace DBChecker;

use DBChecker\InputModules\AbstractDBAL;

interface ModuleWorkerInterface
{
    /**
     * @param AbstractDBAL $dbal
     * @return \Generator|array
     */
    public function run(AbstractDBAL $dbal);
}
<?php

namespace DBChecker;

interface ModuleInterface extends BaseModuleInterface
{
    /**
     * @return ModuleWorkerInterface
     */
    public function getWorker();
}
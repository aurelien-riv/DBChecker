<?php

namespace DBChecker;

interface ModuleWorkerInterface
{
    /**
     * @return \Generator|array
     */
    public function run();
}
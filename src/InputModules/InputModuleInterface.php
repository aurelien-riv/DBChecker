<?php

namespace DBChecker\InputModules;

interface InputModuleInterface extends \DBChecker\BaseModuleInterface
{
    public function getDbal() : AbstractDBAL;
}
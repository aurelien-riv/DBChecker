<?php

define("DBCHECKER_VAR_DIR", __DIR__."/../var/");

if (! is_dir(DBCHECKER_VAR_DIR))
{
    mkdir(DBCHECKER_VAR_DIR);
}
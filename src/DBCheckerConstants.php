<?php

$dir = Phar::running(false);
if (empty($dir))
{
    $dir = __DIR__;
}
define("DBCHECKER_VAR_DIR", "$dir/../var/");

if (! is_dir(DBCHECKER_VAR_DIR))
{
    mkdir(DBCHECKER_VAR_DIR);
}
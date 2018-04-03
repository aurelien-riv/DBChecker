<?php

require_once('DBChecker/DBChecker.php');

$iniPath = $argc == 2 ? $argv[1] : '';

$dbChecker = new \DBChecker\DBChecker($iniPath);

foreach ($dbChecker->run() as $error)
{
    echo get_class($error).' '.$error;
}
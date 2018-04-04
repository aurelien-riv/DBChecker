<?php

use DBChecker\FileCheckMatch;
use DBChecker\RelCheckMatch;

require_once('DBChecker/DBChecker.php');

$iniPath = $argc == 2 ? $argv[1] : '';

$dbChecker = new \DBChecker\DBChecker($iniPath);

foreach ($dbChecker->run() as $error)
{
    if ($error instanceof RelCheckMatch || $error instanceof FileCheckMatch)
    {
        try
        {
            echo $error->toSQLDelete();
        }
        catch(InvalidArgumentException $e)
        {
//            echo get_class($error).' '.$error;
        }
    }
    else
    {
        echo get_class($error).' '.$error;
    }
}
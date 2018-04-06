<?php

use DBChecker\FileCheckMatch;
use DBChecker\RelCheckMatch;

require_once('DBChecker/vendor/autoload.php');
require_once('DBChecker/DBChecker.php');

$dbChecker = new \DBChecker\DBChecker($argv[1]);

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
            echo get_class($error).' '.$error;
        }
    }
    else
    {
        echo get_class($error).' '.$error;
    }
}
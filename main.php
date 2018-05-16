<?php

require_once((strpos(__FILE__, 'phar') ? 'phar://DBChecker.phar' : __DIR__) .'/vendor/autoload.php');

use DBChecker\modules\FileCheck\FileCheckMatch;
use DBChecker\modules\RelCheck\RelCheckMatch;

$dbChecker = new \DBChecker\DBChecker($argv[1]);

foreach ($dbChecker->run() as $error)
{
    echo $error;
}
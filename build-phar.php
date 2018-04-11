<?php

$phar = new \Phar('DBChecker.phar', 0, 'DBChecker.phar');
$phar->setSignatureAlgorithm(\Phar::SHA1);

$phar->startBuffering();

foreach (['src', 'vendor'] as $dir)
{
    $it = new RecursiveDirectoryIterator(__DIR__."/$dir");
    $phar->buildFromIterator(new RecursiveIteratorIterator($it), __DIR__);
}

$phar->setStub(file_get_contents(__DIR__.'/main.php') . "\n__HALT_COMPILER();");

$phar->stopBuffering();
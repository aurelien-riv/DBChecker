<?php

$phar = new \Phar('DBChercker.phar', 0, 'DBChercker.phar');
$phar->setSignatureAlgorithm(\Phar::SHA1);

$phar->startBuffering();

foreach (['src', 'vendor'] as $dir)
{
    $it = new RecursiveDirectoryIterator(__DIR__."/$dir");
    foreach(new RecursiveIteratorIterator($it) as $file) /** @var \DirectoryIterator $file */
    {
        $phar->addFile($file->getPath());
    }
}

$phar->setStub(file_get_contents(__DIR__.'/main.php') . "\n__HALT_COMPILER();");

$phar->stopBuffering();
<?php

namespace DBChecker\modules\PwnedAccountsDetect;

use Symfony\Component\Cache\Adapter\PdoAdapter;

class CacheBackend
{
    private $cache;

    public function __construct($namespace)
    {
        $this->cache = new PdoAdapter("sqlite:".DBCHECKER_VAR_DIR."/dbchecker.sqlite", $namespace);
        try
        {
            $this->cache->createTable();
        }
        catch (\PDOException $e)
        {
            // The table  already exists
        }
    }

    public function isAccountPwned(string $account) : bool
    {
        $item = $this->cache->getItem(hash('md5', $account));
        if ($item->isHit())
        {
            return true;
        }
        return false;
    }

    public function setAccountPwned(string $account) : void
    {
        $item = $this->cache->getItem(hash('md5', $account));
        $item->set(true);
        $this->cache->save($item);
    }
}
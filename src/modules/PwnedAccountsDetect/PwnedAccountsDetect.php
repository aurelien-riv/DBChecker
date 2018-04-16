<?php

namespace DBChecker\modules\PwnedAccountsDetect;

use DBChecker\DBAL\AbstractDBAL;
use DBChecker\ModuleWorkerInterface;

class PwnedAccountsDetect implements ModuleWorkerInterface
{
    private $config;
    private $backend;
    private $cache;

    public function __construct(PwnedAccountsDetectModule $module)
    {
        $this->config = $module->getConfig();
        $this->backend = new HaveIBeenPwnedBackend();
        $this->cache = new CacheBackend($module->getName());
    }

    /**
     * @param AbstractDBAL $dbal
     * @return array|\Generator
     * @throws TlsHandcheckException
     */
    public function run(AbstractDBAL $dbal)
    {
        foreach ($this->config['mapping'] as $mapping)
        {
            $column =$mapping['login_column'];
            $users = $dbal->getDistinctValuesWithoutNulls($mapping['table'], $column);
            foreach ($users as $user)
            {
                yield from $this->checkLogin($user[$column], $dbal->getName(), $mapping['table'], $column);
            }
        }
    }

    /**
     * @param string $login
     * @param string $dbName
     * @param string $table
     * @param string $column
     * @return \Generator
     * @throws TlsHandcheckException
     */
    private function checkLogin(string $login, string $dbName, string $table, string $column)
    {
        if ($this->cache->isAccountPwned($login))
        {
            if (! $this->config['show_new_only'])
            {
                yield new PwnedAccountDetectMatch($dbName, $table, $column, $login);
            }
        }
        else if ($this->backend->isAccountPwned($login))
        {
            $this->cache->setAccountPwned($login);
            yield new PwnedAccountDetectMatch($dbName, $table, $column, $login);
        }
    }
}
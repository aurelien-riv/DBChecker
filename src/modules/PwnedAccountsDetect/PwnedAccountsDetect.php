<?php

namespace DBChecker\modules\PwnedAccountsDetect;

use DBChecker\ModuleWorkerInterface;

class PwnedAccountsDetect implements ModuleWorkerInterface
{
    private $config;
    private $backend;

    public function __construct(PwnedAccountsDetectModule $module)
    {
        $this->config = $module->getConfig();
        $this->backend = new HaveIBeenPwnedBackend();
    }

    /**
     * @param \DBChecker\DBQueries\AbstractDbQueries $dbQueries
     * @return array|\Generator
     * @throws TlsHandcheckException
     */
    public function run(\DBChecker\DBQueries\AbstractDbQueries $dbQueries)
    {
        foreach ($this->config['mapping'] as $mapping)
        {
            $column =$mapping['login_column'];
            $users = $dbQueries->getDistinctValuesWithoutNulls($mapping['table'], $column)
                               ->fetchAll(\PDO::FETCH_OBJ);
            foreach ($users as $user)
            {
                yield from $this->checkLogin($user->{$column}, $dbQueries->getName(), $mapping['table'], $column);
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
        if ($this->backend->isAccountPwned($login))
        {
            yield new PwnedAccountDetectMatch($dbName, $table, $column, $login);
        }
    }
}
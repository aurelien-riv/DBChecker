<?php

namespace DBChecker\modules\FileCheck;

use DBChecker\AbstractMatch;
use DBChecker\Config;
use DBChecker\DBQueries\AbstractDbQueries;
use DBChecker\ModuleWorkerInterface;

class FileCheck implements ModuleWorkerInterface
{
    private $config;

    /**
     * @var FileCheckSftp $sftp
     */
    private $sftp;
    /**
     * @var FileCheckHttp $http
     */
    private $http;

    /**
     * FileCheck constructor.
     * @param FileCheckModule $module
     * @throws \Exception
     */
    public function __construct(FileCheckModule $module)
    {
        $this->config = $module->getConfig();

        if ($this->config['enable_remotes'])
        {
            $this->http = new FileCheckHttp();
        }

        if (! empty($this->config['ssh']))
        {
            $this->sftp = new FileCheckSftp($this->config['ssh']);
        }
    }

    public function run(AbstractDbQueries $dbQueries)
    {
        foreach ($this->config['mapping'] as $mapping)
        {
            $table = key($mapping);
            $path = $mapping[$table];

            $columns = [];
            $innerJoins = [];
            preg_match_all("/\{(" . AbstractDbQueries::IDENTIFIER ."(?:\." . AbstractDbQueries::IDENTIFIER .")?)\}/", $path, $matches);
            foreach ($matches[1] as $match)
            {
                $fragments = mb_split('\.', $match);
                if (count($fragments) == 2)
                {
                    $innerJoins[] = $fragments[0];
                }
                $columns[$match] = null;
            }

            $values = $dbQueries->getDistinctValuesWithJoinColumnsWithoutNulls($table, array_keys($columns), $innerJoins)
                              ->fetchAll(\PDO::FETCH_OBJ);
            foreach ($values as $value)
            {
                $tmpColumns = $columns;
                $tmpPath = preg_replace_callback("/\{(" . AbstractDbQueries::IDENTIFIER ."(?:\." . AbstractDbQueries::IDENTIFIER .")?)\}/", function($match) use ($value, &$tmpColumns) {
                    $tmpColumns[$match[1]] = $value->{$match[1]};
                    return $value->{$match[1]};
                }, $path);

                $error = $this->testFile($dbQueries, $table, $tmpColumns, $tmpPath);
                if ($error instanceof AbstractMatch)
                    yield $error;
            }
        }
    }

    protected function testFile(AbstractDbQueries $dbQueries, $table, $columns, $path)
    {
        $match = true;
        if (preg_match('/^https?:\/\//', $path))
        {
            if ($this->http)
            {
                $status = $this->http->testUrl($path);
                if ($status !== true)
                {
                    $match = new FileCheckURLMatch($dbQueries->getName(), $table, $columns, $path, $status);
                }
            }
        }
        else if ($this->sftp)
        {
            if (! $this->sftp->file_exists($path))
            {
                $match = new FileCheckMatch($dbQueries->getName(), $table, $columns, $path);
            }
        }
        else if (! is_file($path))
        {
            $match = new FileCheckMatch($dbQueries->getName(), $table, $columns, $path);
        }
        return $match;
    }


}
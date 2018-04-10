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

    protected function extractVariablesFromPath($path, &$columns=[], &$innerJoins=[])
    {
        $columnIdentifier = AbstractDbQueries::IDENTIFIER;

        preg_match_all("/\{($columnIdentifier(?:\.$columnIdentifier)?)\}/", $path, $matches);

        foreach ($matches[1] as $match)
        {
            $fragments = mb_split('\.', $match);
            if (count($fragments) == 2)
            {
                $innerJoins[] = $fragments[0];
            }
            $columns[$match] = null;
        }
    }

    protected function replaceVariablesFromPath($path, \stdClass $value, &$columns)
    {
        $columnIdentifier = AbstractDbQueries::IDENTIFIER;
        $pattern = "/\{($columnIdentifier(?:\.$columnIdentifier)?)\}/";

        return preg_replace_callback($pattern, function($match) use ($value, &$columns) {
            $columns[$match[1]] = $value->{$match[1]};
            return $value->{$match[1]};
        }, $path);
    }

    public function run(AbstractDbQueries $dbQueries)
    {
        foreach ($this->config['mapping'] as $mapping)
        {
            $table = key($mapping);
            $path = $mapping[$table];

            $columns = $innerJoins = [];
            $this->extractVariablesFromPath($path, $columns, $innerJoins);

            $values = $dbQueries->getDistinctValuesWithJoinColumnsWithoutNulls($table, array_keys($columns), $innerJoins)
                              ->fetchAll(\PDO::FETCH_OBJ);
            foreach ($values as $value)
            {
                $tmpColumns = $columns;
                $tmpPath = $this->replaceVariablesFromPath($path, $value, $tmpColumns);
                yield from $this->testFile($dbQueries, $table, $tmpColumns, $tmpPath);
            }
        }
    }

    protected function testFile(AbstractDbQueries $dbQueries, $table, $columns, $path)
    {
        if (preg_match('/^https?:\/\//', $path))
        {
            if ($this->http)
            {
                $status = $this->http->testUrl($path);
                if ($status !== true)
                {
                    yield new FileCheckURLMatch($dbQueries->getName(), $table, $columns, $path, $status);
                }
            }
        }
        else if ($this->sftp)
        {
            if (! $this->sftp->file_exists($path))
            {
                yield new FileCheckMatch($dbQueries->getName(), $table, $columns, $path);
            }
        }
        else if (! is_file($path))
        {
            yield new FileCheckMatch($dbQueries->getName(), $table, $columns, $path);
        }
    }


}
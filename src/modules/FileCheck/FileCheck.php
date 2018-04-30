<?php

namespace DBChecker\modules\FileCheck;

use DBChecker\DBAL\AbstractDBAL;
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

    protected function replaceVariablesFromPath($path, $value, &$columns)
    {
        $columnIdentifier = AbstractDbQueries::IDENTIFIER;
        $pattern = "/\{($columnIdentifier(?:\.$columnIdentifier)?)\}/";

        return preg_replace_callback($pattern, function($match) use ($value, &$columns) {
            $columns[$match[1]] = $value[$match[1]];
            return $value[$match[1]];
        }, $path);
    }

    public function run(AbstractDBAL $dbal)
    {
        foreach ($this->config['mapping'] as $mapping)
        {
            $table = key($mapping);
            $path = $mapping[$table];

            $columns = $innerJoins = [];
            $this->extractVariablesFromPath($path, $columns, $innerJoins);

            $values = $dbal->getDistinctValuesWithJoinColumnsWithoutNulls($table, array_keys($columns), $innerJoins);
            foreach ($values as $value)
            {
                $tmpColumns = $columns;
                $tmpPath = $this->replaceVariablesFromPath($path, $value, $tmpColumns);
                yield from $this->testFile($dbal->getName(), $table, $tmpColumns, $tmpPath);
            }
        }
    }

    protected function testUrl(string $dbName, string $table, string $columns, string $path)
    {
        if ($this->http)
        {
            $status = $this->http->testUrl($path);
            if ($status !== true)
            {
                yield new FileCheckURLMatch($dbName, $table, $columns, $path, $status);
            }
        }
    }

    protected function testFile(string $dbName, string $table, string $columns, string $path)
    {
        if (preg_match('/^https?:\/\//', $path))
        {
            yield from $this->testUrl($dbName, $table, $columns, $path);
        }
        else if ($this->sftp)
        {
            if (! $this->sftp->file_exists($path))
            {
                yield new FileCheckMatch($dbName, $table, $columns, $path);
            }
        }
        else if (! is_file($path))
        {
            yield new FileCheckMatch($dbName, $table, $columns, $path);
        }
    }


}
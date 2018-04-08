<?php

namespace DBChecker\modules\FileCheck;

use DBChecker\AbstractMatch;
use DBChecker\Config;
use DBChecker\DBQueries\AbstractDbQueries;
use DBChecker\ModuleWorkerInterface;

class FileCheck implements ModuleWorkerInterface
{
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    protected function doRun(AbstractDbQueries $dbQueries)
    {
        $configuration = $this->config->getFilecheck();
        foreach ($configuration['mapping'] as $mapping)
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

                if (preg_match('/^https?:\/\//', $tmpPath)) // disabled until an option exists to enable it (slows the process down too much when not needed)
                {
                    if ($configuration['enable_remotes'])
                    {
                        $urlStatus = $this->testUrl($dbQueries, $table, $columns, $tmpPath);
                        if ($urlStatus instanceof FileCheckURLMatch)
                            yield $urlStatus;
                    }
                }
                else if (! is_file($path))
                {
                    yield new FileCheckMatch($dbQueries->getName(), $table, $tmpColumns, $tmpPath);
                }
            }
        }
    }

    public function run(AbstractDbQueries $dbQueries)
    {
        $configuration = $this->config->getFilecheck();

        if ($configuration['enable_remotes'])
        {
            stream_context_set_default([
                'http' => [
                    'method' => 'HEAD'
                ]
            ]);
        }

        foreach ($this->doRun($dbQueries) as $msg)
        {
            yield $msg;
        }

        // TODO restore stream_context
    }

    protected function testUrl(AbstractDbQueries $dbQueries, $table, $columns, $path)
    {
        $headers = @get_headers($path);
        // if ! status 200
        if (! preg_match('/HTTP\/\d\.\d 2\d{2}.*/', $headers[0]))
        {
            // if is redirect
            if (preg_match('/HTTP\/\d\.\d 3\d{2}.*/', $headers[0]))
            {
                array_shift($headers);
                // browse the headers to get the status before the redirects
                foreach ($headers as $header)
                {
                    // if status 4xx or 5xx
                    if (preg_match('/HTTP\/\d\.\d (4|5)\d{2}.*/', $header))
                    {
                        return new FileCheckURLMatch($dbQueries->getName(), $table, $columns, $path, substr($header, 9, 3));
                    }
                }
            }
            else
            {
                return new FileCheckURLMatch($dbQueries->getName(), $table, $columns, $path, substr($headers[0], 9, 3));
            }
        }
        return true;
    }
}
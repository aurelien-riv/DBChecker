<?php

namespace DBChecker;

use DBChecker\DBQueries\AbstractDbQueries;

require_once 'FileCheckMatch.php';
require_once 'FileCheckURLMatch.php';

class FileCheck
{
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    protected function doRun()
    {
        $configuration = $this->config->getFilecheck();
        $queries = $this->config->getQueries();
        foreach ($configuration['mapping'] as $setting)
        {
            $columns = [];
            $innerJoins = [];
            preg_match_all("/\{(" . AbstractDbQueries::IDENTIFIER ."(?:\." . AbstractDbQueries::IDENTIFIER .")?)\}/", $setting['path'], $matches);
            foreach ($matches[1] as $match)
            {
                $fragments = mb_split('\.', $match);
                if (count($fragments) == 2)
                {
                    $innerJoins[] = $fragments[0];
                }
                $columns[$match] = null;
            }

            $values = $queries->getDistinctValuesWithJoinColumnsWithoutNulls($setting['table'], array_keys($columns), $innerJoins)
                              ->fetchAll(\PDO::FETCH_OBJ);
            foreach ($values as $value)
            {
                $tmpColumns = $columns;
                $path = preg_replace_callback("/\{(" . AbstractDbQueries::IDENTIFIER ."(?:\." . AbstractDbQueries::IDENTIFIER .")?)\}/", function($match) use ($value, &$tmpColumns) {
                    $tmpColumns[$match[1]] = $value->{$match[1]};
                    return $value->{$match[1]};
                }, $setting['path']);

                if (preg_match('/^https?:\/\//', $path)) // disabled until an option exists to enable it (slows the process down too much when not needed)
                {
                    if ($configuration['settings']['enable_remotes'])
                    {
                        $urlStatus = $this->testUrl($setting, $columns, $path);
                        if ($urlStatus instanceof FileCheckURLMatch)
                            yield $urlStatus;
                    }
                }
                else if (! is_file($path))
                {
                    yield new FileCheckMatch($setting['table'], $tmpColumns, $path);
                }
            }
        }
    }

    public function run()
    {
        $configuration = $this->config->getFilecheck();

        if ($configuration['settings']['enable_remotes'])
        {
            stream_context_set_default([
                'http' => [
                    'method' => 'HEAD'
                ]
            ]);
        }

        foreach ($this->doRun() as $msg)
        {
            yield $msg;
        }

        // TODO restore stream_context
    }

    protected function testUrl($setting, $columns, $path)
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
                        return new FileCheckURLMatch($setting['table'], $columns, $path, substr($header, 9, 3));
                    }
                }
            }
            else
            {
                return new FileCheckURLMatch($setting['table'], $columns, $path, substr($headers[0], 9, 3));
            }
        }
        return true;
    }
}
<?php

namespace DBChecker;

require_once('Config.php');

class DBChecker
{
    private $config;

    public function __construct()
    {
        $this->config = new Config();
    }

    public function run()
    {
        if ($this->config->isScenarioActive("relcheck"))
            foreach ($this->relcheck() as $msg)
                yield $msg;
        if ($this->config->isScenarioActive("filecheck"))
            foreach ($this->filecheck() as $msg)
                yield $msg;
    }

    public function relcheck()
    {
        $queries = $this->config->getQueries();

        $fkeys = $queries->getFks()->fetchAll(\PDO::FETCH_OBJ);
        foreach ($fkeys as $fkey)
        {
            $values = $queries->getDistinctValuesWithoutNulls($fkey->TABLE_NAME, $fkey->REFERENCED_COLUMN_NAME)->fetchAll(\PDO::FETCH_COLUMN);
            foreach ($values as $value)
            {
                $valueExists = $queries->getValue($fkey->REFERENCED_TABLE_NAME, $fkey->REFERENCED_COLUMN_NAME, $value)->fetchAll();
                if (empty($valueExists))
                {
                    yield "{$fkey->TABLE_NAME}.{$fkey->COLUMN_NAME} -> {$fkey->REFERENCED_TABLE_NAME}.{$fkey->REFERENCED_COLUMN_NAME} # $value\n";
                }
            }
        }
    }

    public function filecheck()
    {
        $queries = $this->config->getQueries();
        foreach ($queries->getFilecheckSettings()->fetchAll(\PDO::FETCH_OBJ) as $setting)
        {
            if (! is_dir($setting->basepath))
            {
                yield "{$setting->table}.{$setting->column} : {$setting->basepath} is not a directory - skiping";
            }
            else
            {
                $values = $queries->getDistinctValuesWithoutNulls($setting->table, $setting->column)->fetchAll(\PDO::FETCH_COLUMN);
                foreach ($values as $value)
                {
                    if (! is_file($value))
                        yield "{$setting->table}.{$setting->column} : {$setting->basepath}/{$value} : no such file or directory\n";
                }
            }
        }
    }
}
<?php

namespace DBChecker\modules\FileCheck;

use phpseclib\Crypt\RSA;
use phpseclib\Net\SFTP;

class FileCheckSftp
{
    private $sftp;

    public function __construct($config)
    {
        $this->sftp = new SFTP($config['host'], $config['port']);
        if ($config['pkey_file'])
        {
            $auth = $this->getPrivateKeyAuth($config);
        }
        else if ($config['password'])
        {
            $auth = $this->getPasswordAuth($config);
        }
        else
        {
            throw new \InvalidArgumentException("No pkey_file provided");
        }

        if (! $this->sftp->login($config['user'], $auth))
        {
            throw new \InvalidArgumentException("Cannot connect to the server");
        }
    }

    public function file_exists($path)
    {
        return $this->sftp->file_exists($path);
    }

    protected function promptPassword($message)
    {
        printf("$message: ");
        system('stty -echo');
        $password = trim(fgets(STDIN));
        system('stty echo');
        echo '';
        return $password;
    }

    protected function getPasswordAuth($config)
    {
        $password = $config['password'];
        if ($password === 'prompt')
        {
            $password = $this->promptPassword("SSH password for {$config['user']}");
        }
        return $password;
    }

    protected function getPrivateKeyAuth($config)
    {
        $key = new RSA();
        if (! empty($config['pkey_passphrase']))
        {
            $passPhrase = $config['pkey_passphrase'];
            if ($passPhrase === 'prompt')
            {
                $passPhrase = $this->promptPassword("Private key passphrase");
            }
            $key->setPassword($passPhrase);
        }
        $key->loadKey(file_get_contents($config['pkey_file']));

        return $key;
    }
}
<?php

namespace DBChecker\modules\PwnedAccountsDetect;

class HaveIBeenPwnedBackend
{
    const API_BASEURL = "https://haveibeenpwned.com/api/v2/";

    public function isAccountPwned(string $account) : bool
    {
        $data = $this->breachedaccount($account);
        if (! $data)
        {
            return false;
        }
        foreach ($data as $datum)
        {
            foreach ($datum->DataClasses as $dataClass)
            {
                if ($dataClass === 'Passwords')
                {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param string $account A login or email address
     * @return \stdClass[]
     */
    public function breachedaccount(string $account)
    {
        $ch = curl_init(static::API_BASEURL."/breachedaccount/".$account);
        curl_setopt($ch, CURLOPT_USERAGENT, "DBChecker-PwnedAccountsDetect");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($ch);
        curl_close($ch);
        // Requests to the breaches and pastes APIs are limited to one
        // per every 1500 milliseconds each from any given IP address
        sleep(2);
        return json_decode($data);
    }
}
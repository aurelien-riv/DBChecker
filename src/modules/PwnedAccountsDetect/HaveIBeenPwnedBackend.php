<?php

namespace DBChecker\modules\PwnedAccountsDetect;

class HaveIBeenPwnedBackend
{
    const API_BASEURL = "https://haveibeenpwned.com/api/v2/";

    /**
     * @param string $account
     * @return bool
     * @throws TlsHandcheckException
     */
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
     * @throws TlsHandcheckException
     */
    public function breachedaccount(string $account)
    {
        $curl = curl_init(static::API_BASEURL."/breachedaccount/".$account);
        curl_setopt($curl, CURLOPT_USERAGENT, "DBChecker-PwnedAccountsDetect");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($curl);

        if ($data === false && curl_error($curl) === 'gnutls_handshake() failed: Handshake failed')
        {
            curl_close($curl);
            // This should only happen on Travis CI...
            throw new TlsHandcheckException("gnutls_handshake() failed: Handshake failed");
        }

        curl_close($curl);
        // Requests to the breaches and pastes APIs are limited to one
        // per every 1500 milliseconds each from any given IP address
        sleep(2);
        return json_decode($data);
    }
}
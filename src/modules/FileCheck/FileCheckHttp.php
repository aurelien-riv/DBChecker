<?php

namespace DBChecker\modules\FileCheck;

class FileCheckHttp
{
    private $streamContext;

    public function __construct()
    {
        $this->streamContext = stream_context_create([
            'http' => [
                'method' => 'HEAD'
            ]
        ]);
    }

    public function testUrl($path)
    {
        $headers = @get_headers($path, null, $this->streamContext);
        if ($headers === false)
        {
            return false;
        }
        return $this->getStatusCodeFromHeaders($headers);
    }

    public function getStatusCodeFromHeaders(array $headers)
    {
        $header = array_shift($headers);

        // if ! status 200
        if (! preg_match('/HTTP\/\d\.\d 2\d{2}.*/', $header))
        {
            // if there is no redirect, return the status
            if (! preg_match('/HTTP\/\d\.\d 3\d{2}.*/', $header))
            {
                return substr($header, 9, 3);
            }

            // browse the headers to get the status before the redirects
            return $this->searchErrorStatusInHeaders($headers);
        }
        return true;
    }

    /**
     * @param array $headers The HTTP headers
     * @return bool|string The status code if 4xx or 5xx, true if none found
     * Browse the headers until it finds a HTTP status 4xx or 5xx
     */
    private function searchErrorStatusInHeaders(array $headers)
    {
        foreach ($headers as $header)
        {
            // if status 4xx or 5xx
            if (preg_match('/HTTP\/\d\.\d (4|5)\d{2}.*/', $header))
            {
                return substr($header, 9, 3);
            }
        }
        return true;
    }
}
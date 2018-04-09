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
                        return substr($header, 9, 3);
                    }
                }
            }
            else
            {
                return substr($headers[0], 9, 3);
            }
        }
        return true;
    }
}
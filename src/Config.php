<?php

declare(strict_types=1);

namespace RemoteMerge\Esewa;

class Config
{
    /**
     * The API url for development mode
     */
    public string $apiUrl = 'https://uat.esewa.com.np';

    public function __construct(public string $successUrl, public string $failureUrl, public string $merchantCode = 'EPAYTEST')
    {
        if ($merchantCode !== 'EPAYTEST') {
            // set API URL for production
            $this->apiUrl = 'https://esewa.com.np';
        }
    }
}

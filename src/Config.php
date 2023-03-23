<?php declare(strict_types=1);

namespace Cixware\Esewa;

final class Config
{
    /**
     * API url for development
     */
    public string $apiUrl = 'https://uat.esewa.com.np';

    /**
     * The merchant code provided by eSewa
     */
    public string $merchantCode;

    /**
     * The callback url for successful transaction
     */
    public string $successUrl;

    /**
     * The callback url for failed transaction
     */
    public string $failureUrl;

    public function __construct(string $successUrl, string $failureUrl, ?string $merchantCode)
    {
        $this->successUrl = $successUrl;
        $this->failureUrl = $failureUrl;
        $this->merchantCode = $merchantCode ?? 'EPAYTEST';

        // set API url for production
        if (strtoupper($merchantCode) !== 'EPAYTEST') {
            $this->apiUrl = 'https://esewa.com.np';
        }
    }
}

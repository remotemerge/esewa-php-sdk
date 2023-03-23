<?php declare(strict_types=1);

namespace Cixware\Esewa;

final class Config
{
    /**
     * The API url for development mode
     */
    public string $apiUrl = 'https://uat.esewa.com.np';

    /**
     * The merchant code provided by eSewa
     */
    public string $merchantCode;

    /**
     * The callback URL for successful eSewa payments
     */
    public string $successUrl;

    /**
     * The callback URL for failed eSewa payments
     */
    public string $failureUrl;

    public function __construct(string $successUrl, string $failureUrl, ?string $merchantCode)
    {
        $this->successUrl = $successUrl;
        $this->failureUrl = $failureUrl;
        $this->merchantCode = $merchantCode ?? 'EPAYTEST';

        // set API url for production mode
        if (strtoupper($merchantCode) !== 'EPAYTEST') {
            $this->apiUrl = 'https://esewa.com.np';
        }
    }
}

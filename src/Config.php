<?php

declare(strict_types=1);

namespace RemoteMerge\Esewa;

class Config implements ConfigInterface
{
    /**
     * The merchant code provided by eSewa
     */
    private string $merchantCode = 'EPAYTEST';

    /**
     * The API url for development mode
     */
    private string $apiUrl = 'https://uat.esewa.com.np';

    /**
     * The URL to redirect after successful payment
     */
    private string $successUrl = '';

    /**
     * The URL to redirect after failed payment
     */
    private string $failureUrl = '';

    public function __construct(private readonly array $configs = [])
    {
        // Update the configuration
        $this->merchantCode = $this->configs['merchant_code'] ?? $this->merchantCode;
        $this->successUrl = $this->configs['success_url'] ?? $this->successUrl;
        $this->failureUrl = $this->configs['failure_url'] ?? $this->failureUrl;

        // Set the API URL
        $this->setApiUrl();
    }

    /**
     * Set the API URL based on the merchant code
     */
    private function setApiUrl(): void
    {
        if ($this->getMerchantCode() !== 'EPAYTEST') {
            // set API URL for production
            $this->apiUrl = 'https://esewa.com.np';
        }
    }

    /**
     * Get the merchant code
     */
    public function getMerchantCode(): string
    {
        return $this->merchantCode;
    }

    /**
     * Get the API URL
     */
    public function getApiUrl(): string
    {
        return $this->apiUrl;
    }

    /**
     * Get the URL to redirect after successful payment
     */
    public function getSuccessUrl(): string
    {
        return $this->successUrl;
    }

    /**
     * Get the URL to redirect after failed payment
     */
    public function getFailureUrl(): string
    {
        return $this->failureUrl;
    }
}

<?php

declare(strict_types=1);

namespace RemoteMerge\Esewa;

interface ConfigInterface
{
    /**
     * Config constructor.
     */
    public function __construct(array $configs = []);

    /**
     * Get the merchant code
     */
    public function getMerchantCode(): string;

    /**
     * Get the API URL
     */
    public function getApiUrl(): string;

    /**
     * Get the URL to redirect after successful payment
     */
    public function getSuccessUrl(): string;

    /**
     * Get the URL to redirect after failed payment
     */
    public function getFailureUrl(): string;
}

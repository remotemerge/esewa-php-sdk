<?php

declare(strict_types=1);

namespace RemoteMerge\Esewa;

use RemoteMerge\Esewa\Exceptions\EsewaException;

abstract class AbstractPayment
{
    /**
     * The eSewa environment test or production.
     */
    protected string $environment = 'test';

    /**
     * The merchant or product code provided by eSewa.
     */
    protected string $productCode;

    /**
     * The secret key for signature generation.
     */
    protected string $secretKey;

    /**
     * Base URLs for different environments.
     */
    protected const BASE_URLS = [
        'test' => [
            'epay' => 'https://rc-epay.esewa.com.np',
            'token' => 'https://uat.esewa.com.np',
        ],
        'production' => [
            'epay' => 'https://epay.esewa.com.np',
            'token' => 'https://esewa.com.np',
        ],
    ];

    /**
     * Validates the amount.
     *
     * @param float $amount The amount to validate.
     * @throws EsewaException If the amount is invalid.
     */
    protected function validateAmount(float $amount): void
    {
        if ($amount <= 0) {
            throw new EsewaException('Amount must be greater than 0.');
        }
    }

    /**
     * Validates the transaction UUID.
     *
     * @param string $uuid The UUID to validate.
     * @throws EsewaException If the UUID is invalid.
     */
    protected function validateTransactionUuid(string $uuid): void
    {
        if (preg_match('/^[a-zA-Z0-9\-]+$/', $uuid) !== 1) {
            throw new EsewaException('Transaction UUID must be alphanumeric and may contain hyphens only.');
        }
    }

    /**
     * Generates HMAC signature using SHA256.
     *
     * @param string $data The data to sign.
     * @return string The base64 encoded signature.
     */
    protected function generateSignature(string $data): string
    {
        return base64_encode(hash_hmac('sha256', $data, $this->secretKey, true));
    }

    /**
     * Gets the base URL for the current environment.
     *
     * @param string $type The type of API (epay or token).
     * @return string The base URL.
     */
    protected function getBaseUrl(string $type): string
    {
        return self::BASE_URLS[$this->environment][$type];
    }

    /**
     * Decodes base64 response from eSewa.
     *
     * @param string $encodedData The base64 encoded data.
     * @throws EsewaException If decoding fails.
     * @return array<string, mixed> The decoded data.
     */
    protected function decodeResponse(string $encodedData): array
    {
        $decoded = base64_decode($encodedData, true);
        if ($decoded === false) {
            throw new EsewaException('Failed to decode response data.');
        }

        $data = json_decode($decoded, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new EsewaException('Invalid JSON in response: ' . json_last_error_msg());
        }

        return $data;
    }

    /**
     * Validates common configuration options.
     *
     * @param array<string, mixed> $options The configuration options.
     * @throws EsewaException If validation fails.
     */
    protected function validateCommonConfiguration(array $options): void
    {
        if (isset($options['environment'])) {
            if (!in_array($options['environment'], ['test', 'production'], true)) {
                throw new EsewaException('Environment must be either "test" or "production".');
            }

            $this->environment = $options['environment'];
        }

        if (!isset($options['product_code'])) {
            throw new EsewaException('Product code is required.');
        }

        $this->productCode = $options['product_code'];

        if (!isset($options['secret_key'])) {
            throw new EsewaException('Secret key is required.');
        }

        $this->secretKey = $options['secret_key'];
    }

    /**
     * Validates the response from the API.
     *
     * @param array<string, mixed> $resData The response data.
     * @throws EsewaException
     */
    protected function validateResponse(array $resData): void
    {
        if (isset($resData['code']) && $resData['code'] !== 0) {
            throw new EsewaException('API Error: ' . ($resData['message'] ?? 'Unknown payment gateway error'));
        }
    }
}

<?php

declare(strict_types=1);

namespace RemoteMerge\Esewa;

use RemoteMerge\Esewa\Exceptions\EsewaException;
use RemoteMerge\Esewa\Utils\Validation;

abstract class AbstractPayment
{
    use Validation;
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
     * Validates common configuration options.
     *
     * @param array<string, mixed> $options The configuration options.
     * @throws EsewaException If validation fails.
     */
    protected function validateCommonConfiguration(array $options): void
    {
        if (isset($options['environment'])) {
            $this->validateEnvironment($options['environment']);
            $this->environment = $options['environment'];
        }

        $this->validateRequiredField($options, 'product_code', 'Product code');
        $this->validateProductCode($options['product_code']);
        $this->productCode = $options['product_code'];

        $this->validateRequiredField($options, 'secret_key', 'Secret key');
        $this->validateSecretKey($options['secret_key']);
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
        $this->validateApiResponse($resData);
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

}

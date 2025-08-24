<?php

declare(strict_types=1);

namespace RemoteMerge\Esewa\Utils;

use RemoteMerge\Esewa\Exceptions\EsewaException;

trait Validation
{
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
     * Validates the transaction code.
     *
     * @param string $code The transaction code to validate.
     * @throws EsewaException If the code is invalid.
     */
    protected function validateTransactionCode(string $code): void
    {
        if (empty(trim($code))) {
            throw new EsewaException('Transaction code cannot be empty.');
        }
    }

    /**
     * Validates the request ID.
     *
     * @param string $requestId The request ID to validate.
     * @throws EsewaException If the request ID is invalid.
     */
    protected function validateRequestId(string $requestId): void
    {
        if (empty(trim($requestId))) {
            throw new EsewaException('Request ID cannot be empty.');
        }
    }

    /**
     * Validates the environment.
     *
     * @param string $environment The environment to validate.
     * @throws EsewaException If the environment is invalid.
     */
    protected function validateEnvironment(string $environment): void
    {
        if (!in_array($environment, ['test', 'production'], true)) {
            throw new EsewaException('Environment must be either "test" or "production".');
        }
    }

    /**
     * Validates the product code.
     *
     * @param string $productCode The product code to validate.
     * @throws EsewaException If the product code is invalid.
     */
    protected function validateProductCode(string $productCode): void
    {
        if (empty(trim($productCode))) {
            throw new EsewaException('Product code cannot be empty.');
        }
    }

    /**
     * Validates the secret key.
     *
     * @param string $secretKey The secret key to validate.
     * @throws EsewaException If the secret key is invalid.
     */
    protected function validateSecretKey(string $secretKey): void
    {
        if (empty(trim($secretKey))) {
            throw new EsewaException('Secret key cannot be empty.');
        }
    }

    /**
     * Validates required field existence.
     *
     * @param array<string, mixed> $data The data array to check.
     * @param string $field The field name to validate.
     * @param string $fieldLabel The human-readable field label for an error message.
     * @throws EsewaException If the field is missing.
     */
    protected function validateRequiredField(array $data, string $field, string $fieldLabel): void
    {
        if (!isset($data[$field])) {
            throw new EsewaException($fieldLabel . ' is required.');
        }
    }

    /**
     * Validates API response code.
     *
     * @param array<string, mixed> $resData The response data.
     * @throws EsewaException If response indicates error.
     */
    protected function validateApiResponse(array $resData): void
    {
        if (isset($resData['code']) && $resData['code'] !== 0) {
            throw new EsewaException('API Error: ' . ($resData['message'] ?? 'Unknown payment gateway error'));
        }
    }

    /**
     * Validates TokenPay transaction data (common pattern for payment and status).
     *
     * @param array<string, mixed> $data The transaction data to validate.
     * @throws EsewaException If validation fails.
     */
    protected function validateTokenPayTransactionData(array $data): void
    {
        $this->validateRequiredField($data, 'request_id', 'Request ID');
        $this->validateRequestId($data['request_id']);
        
        $this->validateRequiredField($data, 'amount', 'Amount');
        $this->validateAmount((float) $data['amount']);
        
        $this->validateRequiredField($data, 'transaction_code', 'Transaction code');
        $this->validateTransactionCode($data['transaction_code']);
    }
}

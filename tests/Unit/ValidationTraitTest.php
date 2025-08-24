<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use RemoteMerge\Esewa\Exceptions\EsewaException;
use RemoteMerge\Esewa\Utils\Validation;

/**
 * @covers \RemoteMerge\Esewa\Utils\Validation
 */
class ValidationTraitTest extends TestCase
{
    private ValidationTestClass $validationTestClass;

    protected function setUp(): void
    {
        $this->validationTestClass = new ValidationTestClass();
    }

    /**
     * @throws EsewaException
     */
    public function testValidateAmountWithValidAmount(): void
    {
        $this->validationTestClass->testValidateAmount(100.50);
        $this->validationTestClass->testValidateAmount(1.00);
        $this->validationTestClass->testValidateAmount(999999.99);

        $this->assertTrue(true); // No exception means success
    }

    public function testValidateAmountWithZeroAmount(): void
    {
        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Amount must be greater than 0.');

        $this->validationTestClass->testValidateAmount(0.0);
    }

    public function testValidateAmountWithNegativeAmount(): void
    {
        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Amount must be greater than 0.');

        $this->validationTestClass->testValidateAmount(-10.50);
    }

    /**
     * @throws EsewaException
     */
    public function testValidateTransactionUuidWithValidUuid(): void
    {
        $this->validationTestClass->testValidateTransactionUuid('abc123-def456');
        $this->validationTestClass->testValidateTransactionUuid('123456789');
        $this->validationTestClass->testValidateTransactionUuid('UUID-123-ABC');
        $this->validationTestClass->testValidateTransactionUuid('simple-uuid');

        $this->assertTrue(true); // No exception means success
    }

    public function testValidateTransactionUuidWithInvalidCharacters(): void
    {
        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Transaction UUID must be alphanumeric and may contain hyphens only.');

        $this->validationTestClass->testValidateTransactionUuid('uuid@123');
    }

    public function testValidateTransactionUuidWithSpecialCharacters(): void
    {
        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Transaction UUID must be alphanumeric and may contain hyphens only.');

        $this->validationTestClass->testValidateTransactionUuid('uuid#123!');
    }

    public function testValidateTransactionUuidWithSpaces(): void
    {
        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Transaction UUID must be alphanumeric and may contain hyphens only.');

        $this->validationTestClass->testValidateTransactionUuid('uuid 123');
    }

    /**
     * @throws EsewaException
     */
    public function testValidateTransactionCodeWithValidCode(): void
    {
        $this->validationTestClass->testValidateTransactionCode('TXN123456');
        $this->validationTestClass->testValidateTransactionCode('valid-code');
        $this->validationTestClass->testValidateTransactionCode('CODE_123');

        $this->assertTrue(true); // No exception means success
    }

    public function testValidateTransactionCodeWithEmptyCode(): void
    {
        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Transaction code cannot be empty.');

        $this->validationTestClass->testValidateTransactionCode('');
    }

    public function testValidateTransactionCodeWithWhitespaceOnlyCode(): void
    {
        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Transaction code cannot be empty.');

        $this->validationTestClass->testValidateTransactionCode('   ');
    }

    /**
     * @throws EsewaException
     */
    public function testValidateRequestIdWithValidId(): void
    {
        $this->validationTestClass->testValidateRequestId('REQ123456');
        $this->validationTestClass->testValidateRequestId('request-id-123');
        $this->validationTestClass->testValidateRequestId('valid_request');

        $this->assertTrue(true); // No exception means success
    }

    public function testValidateRequestIdWithEmptyId(): void
    {
        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Request ID cannot be empty.');

        $this->validationTestClass->testValidateRequestId('');
    }

    public function testValidateRequestIdWithWhitespaceOnlyId(): void
    {
        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Request ID cannot be empty.');

        $this->validationTestClass->testValidateRequestId('   ');
    }

    /**
     * @throws EsewaException
     */
    public function testValidateEnvironmentWithValidEnvironments(): void
    {
        $this->validationTestClass->testValidateEnvironment('test');
        $this->validationTestClass->testValidateEnvironment('production');

        $this->assertTrue(true); // No exception means success
    }

    public function testValidateEnvironmentWithInvalidEnvironment(): void
    {
        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Environment must be either "test" or "production".');

        $this->validationTestClass->testValidateEnvironment('staging');
    }

    public function testValidateEnvironmentWithCaseSensitivity(): void
    {
        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Environment must be either "test" or "production".');

        $this->validationTestClass->testValidateEnvironment('TEST');
    }

    public function testValidateEnvironmentWithEmptyEnvironment(): void
    {
        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Environment must be either "test" or "production".');

        $this->validationTestClass->testValidateEnvironment('');
    }

    /**
     * @throws EsewaException
     */
    public function testValidateProductCodeWithValidCode(): void
    {
        $this->validationTestClass->testValidateProductCode('EPAYTEST');
        $this->validationTestClass->testValidateProductCode('PROD123');
        $this->validationTestClass->testValidateProductCode('valid-product-code');

        $this->assertTrue(true); // No exception means success
    }

    public function testValidateProductCodeWithEmptyCode(): void
    {
        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Product code cannot be empty.');

        $this->validationTestClass->testValidateProductCode('');
    }

    public function testValidateProductCodeWithWhitespaceOnlyCode(): void
    {
        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Product code cannot be empty.');

        $this->validationTestClass->testValidateProductCode('   ');
    }

    /**
     * @throws EsewaException
     */
    public function testValidateSecretKeyWithValidKey(): void
    {
        $this->validationTestClass->testValidateSecretKey('BhwIWVKBJdzXAz9SaBjKyQNGwFFgQAWJYARKEMOITYHggE=');
        $this->validationTestClass->testValidateSecretKey('valid-secret-key');
        $this->validationTestClass->testValidateSecretKey('123456789');

        $this->assertTrue(true); // No exception means success
    }

    public function testValidateSecretKeyWithEmptyKey(): void
    {
        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Secret key cannot be empty.');

        $this->validationTestClass->testValidateSecretKey('');
    }

    public function testValidateSecretKeyWithWhitespaceOnlyKey(): void
    {
        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Secret key cannot be empty.');

        $this->validationTestClass->testValidateSecretKey('   ');
    }

    /**
     * @throws EsewaException
     */
    public function testValidateRequiredFieldWithValidField(): void
    {
        $data = ['field1' => 'value1', 'field2' => 'value2'];

        $this->validationTestClass->testValidateRequiredField($data, 'field1', 'Field 1');
        $this->validationTestClass->testValidateRequiredField($data, 'field2', 'Field 2');

        $this->assertTrue(true); // No exception means success
    }

    public function testValidateRequiredFieldWithMissingField(): void
    {
        $data = ['field1' => 'value1'];

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Field 2 is required.');

        $this->validationTestClass->testValidateRequiredField($data, 'field2', 'Field 2');
    }

    public function testValidateRequiredFieldWithNullValue(): void
    {
        $data = ['field1' => null];

        // isset() returns false for null values in PHP, so this should throw an exception
        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Field 1 is required.');

        $this->validationTestClass->testValidateRequiredField($data, 'field1', 'Field 1');
    }

    /**
     * @throws EsewaException
     */
    public function testValidateApiResponseWithSuccessCode(): void
    {
        $responseData = ['code' => 0, 'message' => 'Success'];

        $this->validationTestClass->testValidateApiResponse($responseData);

        $this->assertTrue(true); // No exception means success
    }

    public function testValidateApiResponseWithErrorCode(): void
    {
        $responseData = ['code' => 1, 'message' => 'Payment failed'];

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('API Error: Payment failed');

        $this->validationTestClass->testValidateApiResponse($responseData);
    }

    public function testValidateApiResponseWithErrorCodeWithoutMessage(): void
    {
        $responseData = ['code' => 1];

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('API Error: Unknown payment gateway error');

        $this->validationTestClass->testValidateApiResponse($responseData);
    }

    /**
     * @throws EsewaException
     */
    public function testValidateApiResponseWithMissingCode(): void
    {
        $responseData = ['message' => 'Some message'];

        // Should not throw exception if code is missing
        $this->validationTestClass->testValidateApiResponse($responseData);

        $this->assertTrue(true);
    }

    /**
     * @throws EsewaException
     */
    public function testValidateTokenPayTransactionDataWithValidData(): void
    {
        $data = [
            'request_id' => 'REQ123456',
            'amount' => 100.50,
            'transaction_code' => 'TXN789',
        ];

        $this->validationTestClass->testValidateTokenPayTransactionData($data);

        $this->assertTrue(true); // No exception means success
    }

    public function testValidateTokenPayTransactionDataWithMissingRequestId(): void
    {
        $data = [
            'amount' => 100.50,
            'transaction_code' => 'TXN789',
        ];

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Request ID is required.');

        $this->validationTestClass->testValidateTokenPayTransactionData($data);
    }

    public function testValidateTokenPayTransactionDataWithInvalidAmount(): void
    {
        $data = [
            'request_id' => 'REQ123456',
            'amount' => -10.0,
            'transaction_code' => 'TXN789',
        ];

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Amount must be greater than 0.');

        $this->validationTestClass->testValidateTokenPayTransactionData($data);
    }

    public function testValidateTokenPayTransactionDataWithMissingTransactionCode(): void
    {
        $data = [
            'request_id' => 'REQ123456',
            'amount' => 100.50,
        ];

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Transaction code is required.');

        $this->validationTestClass->testValidateTokenPayTransactionData($data);
    }

    /**
     * @throws EsewaException
     */
    public function testValidateTokenPayTransactionDataWithStringAmount(): void
    {
        $data = [
            'request_id' => 'REQ123456',
            'amount' => '100.50',
            'transaction_code' => 'TXN789',
        ];

        $this->validationTestClass->testValidateTokenPayTransactionData($data);

        $this->assertTrue(true); // Should work with string amounts that can be cast to float
    }

    public function testValidateTokenPayTransactionDataWithZeroAmount(): void
    {
        $data = [
            'request_id' => 'REQ123456',
            'amount' => 0,
            'transaction_code' => 'TXN789',
        ];

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Amount must be greater than 0.');

        $this->validationTestClass->testValidateTokenPayTransactionData($data);
    }
}

class ValidationTestClass
{
    use Validation;

    /**
     * @throws EsewaException
     */
    public function testValidateAmount(float $amount): void
    {
        $this->validateAmount($amount);
    }

    /**
     * @throws EsewaException
     */
    public function testValidateTransactionUuid(string $uuid): void
    {
        $this->validateTransactionUuid($uuid);
    }

    /**
     * @throws EsewaException
     */
    public function testValidateTransactionCode(string $code): void
    {
        $this->validateTransactionCode($code);
    }

    /**
     * @throws EsewaException
     */
    public function testValidateRequestId(string $requestId): void
    {
        $this->validateRequestId($requestId);
    }

    /**
     * @throws EsewaException
     */
    public function testValidateEnvironment(string $environment): void
    {
        $this->validateEnvironment($environment);
    }

    /**
     * @throws EsewaException
     */
    public function testValidateProductCode(string $productCode): void
    {
        $this->validateProductCode($productCode);
    }

    /**
     * @throws EsewaException
     */
    public function testValidateSecretKey(string $secretKey): void
    {
        $this->validateSecretKey($secretKey);
    }

    /**
     * @throws EsewaException
     */
    public function testValidateRequiredField(array $data, string $field, string $fieldLabel): void
    {
        $this->validateRequiredField($data, $field, $fieldLabel);
    }

    /**
     * @throws EsewaException
     */
    public function testValidateApiResponse(array $resData): void
    {
        $this->validateApiResponse($resData);
    }

    /**
     * @throws EsewaException
     */
    public function testValidateTokenPayTransactionData(array $data): void
    {
        $this->validateTokenPayTransactionData($data);
    }
}

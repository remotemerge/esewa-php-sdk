<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RemoteMerge\Esewa\AbstractPayment;
use RemoteMerge\Esewa\Exceptions\EsewaException;

#[CoversClass(AbstractPayment::class)]
final class AbstractPaymentTest extends TestCase
{
    private AbstractPaymentTestClass $paymentTestClass;

    protected function setUp(): void
    {
        $this->paymentTestClass = new AbstractPaymentTestClass();
    }

    public function testGenerateSignatureWithValidData(): void
    {
        $data = 'test-data-for-signature';
        $secretKey = 'BhwIWVKBJdzXAz9SaBjKyQNGwFFgQAWJYARKEMOITYHggE=';

        $this->paymentTestClass->setSecretKey($secretKey);
        $signature = $this->paymentTestClass->testGenerateSignature($data);

        $expectedSignature = base64_encode(hash_hmac('sha256', $data, $secretKey, true));

        $this->assertSame($expectedSignature, $signature);
        $this->assertNotEmpty($signature);
    }

    public function testGenerateSignatureConsistency(): void
    {
        $data = 'consistent-data';
        $secretKey = 'test-secret-key';

        $this->paymentTestClass->setSecretKey($secretKey);
        $signature1 = $this->paymentTestClass->testGenerateSignature($data);
        $signature2 = $this->paymentTestClass->testGenerateSignature($data);

        $this->assertSame($signature1, $signature2);
    }

    public function testGenerateSignatureDifferentData(): void
    {
        $secretKey = 'test-secret-key';
        $this->paymentTestClass->setSecretKey($secretKey);

        $signature1 = $this->paymentTestClass->testGenerateSignature('data1');
        $signature2 = $this->paymentTestClass->testGenerateSignature('data2');

        $this->assertNotSame($signature1, $signature2);
    }

    /**
     * @throws EsewaException
     */
    public function testValidateCommonConfigurationWithValidOptions(): void
    {
        $options = [
            'product_code' => 'EPAYTEST',
            'secret_key' => 'BhwIWVKBJdzXAz9SaBjKyQNGwFFgQAWJYARKEMOITYHggE=',
            'environment' => 'test',
        ];

        $this->paymentTestClass->testValidateCommonConfiguration($options);

        $this->assertSame('test', $this->paymentTestClass->getEnvironment());
        $this->assertSame('EPAYTEST', $this->paymentTestClass->getProductCode());
        $this->assertSame('BhwIWVKBJdzXAz9SaBjKyQNGwFFgQAWJYARKEMOITYHggE=', $this->paymentTestClass->getSecretKey());
    }

    /**
     * @throws EsewaException
     */
    public function testValidateCommonConfigurationWithProductionEnvironment(): void
    {
        $options = [
            'product_code' => 'PROD123',
            'secret_key' => 'production-secret-key',
            'environment' => 'production',
        ];

        $this->paymentTestClass->testValidateCommonConfiguration($options);

        $this->assertSame('production', $this->paymentTestClass->getEnvironment());
    }

    /**
     * @throws EsewaException
     */
    public function testValidateCommonConfigurationDefaultsToTestEnvironment(): void
    {
        $options = [
            'product_code' => 'TEST123',
            'secret_key' => 'test-secret-key',
        ];

        $this->paymentTestClass->testValidateCommonConfiguration($options);

        $this->assertSame('test', $this->paymentTestClass->getEnvironment());
    }

    public function testValidateCommonConfigurationWithInvalidEnvironment(): void
    {
        $options = [
            'product_code' => 'TEST123',
            'secret_key' => 'test-secret-key',
            'environment' => 'staging',
        ];

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Environment must be either "test" or "production".');

        $this->paymentTestClass->testValidateCommonConfiguration($options);
    }

    public function testValidateCommonConfigurationWithMissingProductCode(): void
    {
        $options = [
            'secret_key' => 'test-secret-key',
        ];

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Product code is required.');

        $this->paymentTestClass->testValidateCommonConfiguration($options);
    }

    public function testValidateCommonConfigurationWithEmptyProductCode(): void
    {
        $options = [
            'product_code' => '',
            'secret_key' => 'test-secret-key',
        ];

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Product code cannot be empty.');

        $this->paymentTestClass->testValidateCommonConfiguration($options);
    }

    public function testValidateCommonConfigurationWithMissingSecretKey(): void
    {
        $options = [
            'product_code' => 'TEST123',
        ];

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Secret key is required.');

        $this->paymentTestClass->testValidateCommonConfiguration($options);
    }

    public function testValidateCommonConfigurationWithEmptySecretKey(): void
    {
        $options = [
            'product_code' => 'TEST123',
            'secret_key' => '',
        ];

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Secret key cannot be empty.');

        $this->paymentTestClass->testValidateCommonConfiguration($options);
    }

    /**
     * @throws EsewaException
     */
    public function testValidateResponseWithSuccessResponse(): void
    {
        $responseData = [
            'code' => 0,
            'message' => 'Success',
        ];

        $this->paymentTestClass->testValidateResponse($responseData);

        $this->assertTrue(true); // No exception means success
    }

    public function testValidateResponseWithErrorResponse(): void
    {
        $responseData = [
            'code' => 1,
            'message' => 'Payment failed',
        ];

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('API Error: Payment failed');

        $this->paymentTestClass->testValidateResponse($responseData);
    }

    public function testGetBaseUrlForTestEpay(): void
    {
        $this->paymentTestClass->setEnvironment('test');
        $baseUrl = $this->paymentTestClass->testGetBaseUrl('epay');

        $this->assertSame('https://rc-epay.esewa.com.np', $baseUrl);
    }

    public function testGetBaseUrlForTestToken(): void
    {
        $this->paymentTestClass->setEnvironment('test');
        $baseUrl = $this->paymentTestClass->testGetBaseUrl('token');

        $this->assertSame('https://uat.esewa.com.np', $baseUrl);
    }

    public function testGetBaseUrlForProductionEpay(): void
    {
        $this->paymentTestClass->setEnvironment('production');
        $baseUrl = $this->paymentTestClass->testGetBaseUrl('epay');

        $this->assertSame('https://epay.esewa.com.np', $baseUrl);
    }

    public function testGetBaseUrlForProductionToken(): void
    {
        $this->paymentTestClass->setEnvironment('production');
        $baseUrl = $this->paymentTestClass->testGetBaseUrl('token');

        $this->assertSame('https://esewa.com.np', $baseUrl);
    }

    /**
     * @throws EsewaException
     */
    public function testDecodeResponseWithValidBase64Json(): void
    {
        $data = ['message' => 'Success', 'code' => 0, 'data' => 'test'];
        $encodedData = base64_encode(json_encode($data));

        $decodedData = $this->paymentTestClass->testDecodeResponse($encodedData);

        $this->assertSame($data, $decodedData);
    }

    public function testDecodeResponseWithInvalidBase64(): void
    {
        $invalidBase64 = 'invalid-base64-data!!!';

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Failed to decode response data.');

        $this->paymentTestClass->testDecodeResponse($invalidBase64);
    }

    public function testDecodeResponseWithInvalidJson(): void
    {
        $invalidJsonBase64 = base64_encode('invalid json data {');

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessageMatches('/Invalid JSON in response:/');

        $this->paymentTestClass->testDecodeResponse($invalidJsonBase64);
    }

    public function testDecodeResponseWithEmptyString(): void
    {
        $this->expectException(EsewaException::class);
        $this->expectExceptionMessageMatches('/Invalid JSON in response:/');

        $this->paymentTestClass->testDecodeResponse('');
    }

    /**
     * @throws EsewaException
     */
    public function testDecodeResponseWithComplexJsonData(): void
    {
        $complexData = [
            'transaction' => [
                'id' => '12345',
                'amount' => 100.50,
                'status' => 'success',
            ],
            'merchant' => [
                'code' => 'MERCHANT123',
                'name' => 'Test Merchant',
            ],
            'metadata' => [
                'key1' => 'value1',
                'key2' => 'value2',
            ],
        ];

        $encodedData = base64_encode(json_encode($complexData));
        $decodedData = $this->paymentTestClass->testDecodeResponse($encodedData);

        $this->assertSame($complexData, $decodedData);
    }

    /**
     * @throws EsewaException
     */
    public function testDecodeResponseWithUnicodeCharacters(): void
    {
        $unicodeData = [
            'message' => 'Payment successful: रुपैया',
            'currency' => 'NPR',
            'symbols' => '€£¥₹',
        ];

        $encodedData = base64_encode(json_encode($unicodeData));
        $decodedData = $this->paymentTestClass->testDecodeResponse($encodedData);

        $this->assertSame($unicodeData, $decodedData);
    }

    public function testSignatureWithSpecialCharacters(): void
    {
        $specialData = 'data-with-special-chars: !@#$%^&*()';
        $secretKey = 'secret-key-with-chars: !@#$%';

        $this->paymentTestClass->setSecretKey($secretKey);
        $signature = $this->paymentTestClass->testGenerateSignature($specialData);

        $this->assertNotEmpty($signature);
        $this->assertIsString($signature);
    }

    public function testSignatureWithEmptyData(): void
    {
        $secretKey = 'test-secret-key';

        $this->paymentTestClass->setSecretKey($secretKey);
        $signature = $this->paymentTestClass->testGenerateSignature('');

        $expectedSignature = base64_encode(hash_hmac('sha256', '', $secretKey, true));

        $this->assertSame($expectedSignature, $signature);
    }

    public function testSignatureWithVeryLongData(): void
    {
        $longData = str_repeat('test-data-', 1000); // 10,000 characters
        $secretKey = 'test-secret-key';

        $this->paymentTestClass->setSecretKey($secretKey);
        $signature = $this->paymentTestClass->testGenerateSignature($longData);

        $this->assertNotEmpty($signature);
        $this->assertIsString($signature);
    }
}

// Concrete test class for testing AbstractPayment
class AbstractPaymentTestClass extends AbstractPayment
{
    public function testGenerateSignature(string $data): string
    {
        return $this->generateSignature($data);
    }

    /**
     * @throws EsewaException
     */
    public function testValidateCommonConfiguration(array $options): void
    {
        $this->validateCommonConfiguration($options);
    }

    /**
     * @throws EsewaException
     */
    public function testValidateResponse(array $resData): void
    {
        $this->validateResponse($resData);
    }

    public function testGetBaseUrl(string $type): string
    {
        return $this->getBaseUrl($type);
    }

    /**
     * @throws EsewaException
     */
    public function testDecodeResponse(string $encodedData): array
    {
        return $this->decodeResponse($encodedData);
    }

    // Helper methods to access protected properties for testing
    public function setSecretKey(string $secretKey): void
    {
        $this->secretKey = $secretKey;
    }

    public function setEnvironment(string $environment): void
    {
        $this->environment = $environment;
    }

    public function getEnvironment(): string
    {
        return $this->environment;
    }

    public function getProductCode(): string
    {
        return $this->productCode;
    }

    public function getSecretKey(): string
    {
        return $this->secretKey;
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RemoteMerge\Esewa\Epay\Epay;
use RemoteMerge\Esewa\Epay\EpayInterface;
use RemoteMerge\Esewa\Exceptions\EsewaException;
use RemoteMerge\Esewa\Http\HttpClientInterface;

#[CoversClass(Epay::class)]
final class EpayTest extends TestCase
{
    private Epay $epay;

    private MockObject&HttpClientInterface $httpClientMock;

    private array $validConfiguration;

    protected function setUp(): void
    {
        $this->httpClientMock = $this->createMock(HttpClientInterface::class);
        $this->epay = new Epay($this->httpClientMock);

        $this->validConfiguration = [
            'product_code' => 'EPAYTEST',
            'secret_key' => 'BhwIWVKBJdzXAz9SaBjKyQNGwFFgQAWJYARKEMOITYHggE=',
            'success_url' => 'https://example.com/success',
            'failure_url' => 'https://example.com/failure',
            'environment' => 'test',
        ];
    }

    public function testImplementsEpayInterface(): void
    {
        $this->assertInstanceOf(EpayInterface::class, $this->epay);
    }

    public function testConstructorWithoutHttpClient(): void
    {
        $epay = new Epay();
        $this->assertInstanceOf(Epay::class, $epay);
    }

    /**
     * @throws EsewaException
     */
    public function testConfigureWithValidOptions(): void
    {
        $this->epay->configure($this->validConfiguration);

        $this->assertSame('test', $this->epay->getEnvironment());
        $this->assertSame('EPAYTEST', $this->epay->getProductCode());
    }

    public function testConfigureThrowsExceptionForMissingSuccessUrl(): void
    {
        $options = $this->validConfiguration;
        unset($options['success_url']);

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Success URL is required.');

        $this->epay->configure($options);
    }

    public function testConfigureThrowsExceptionForMissingFailureUrl(): void
    {
        $options = $this->validConfiguration;
        unset($options['failure_url']);

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Failure URL is required.');

        $this->epay->configure($options);
    }

    /**
     * @throws EsewaException
     */
    public function testGetEnvironmentAfterConfiguration(): void
    {
        $this->epay->configure($this->validConfiguration);

        $this->assertSame('test', $this->epay->getEnvironment());
    }

    /**
     * @throws EsewaException
     */
    public function testGetProductCodeAfterConfiguration(): void
    {
        $this->epay->configure($this->validConfiguration);

        $this->assertSame('EPAYTEST', $this->epay->getProductCode());
    }

    /**
     * @throws EsewaException
     */
    public function testCreatePaymentWithValidData(): void
    {
        $this->epay->configure($this->validConfiguration);

        $paymentData = [
            'amount' => 100.50,
            'transaction_uuid' => 'test-uuid-12345',
        ];

        $result = $this->epay->createPayment($paymentData);

        $this->assertArrayHasKey('amount', $result);
        $this->assertArrayHasKey('total_amount', $result);
        $this->assertArrayHasKey('transaction_uuid', $result);
        $this->assertArrayHasKey('product_code', $result);
        $this->assertArrayHasKey('signature', $result);
        $this->assertArrayHasKey('signed_field_names', $result);
        $this->assertArrayHasKey('success_url', $result);
        $this->assertArrayHasKey('failure_url', $result);

        $this->assertEqualsWithDelta(100.50, $result['amount'], PHP_FLOAT_EPSILON);
        $this->assertEqualsWithDelta(100.50, $result['total_amount'], PHP_FLOAT_EPSILON);
        $this->assertEquals('test-uuid-12345', $result['transaction_uuid']);
        $this->assertEquals('EPAYTEST', $result['product_code']);
        $this->assertEquals('https://example.com/success', $result['success_url']);
        $this->assertEquals('https://example.com/failure', $result['failure_url']);
    }

    /**
     * @throws EsewaException
     */
    public function testCreatePaymentWithTaxAmountAndCharges(): void
    {
        $this->epay->configure($this->validConfiguration);

        $paymentData = [
            'amount' => 100.00,
            'tax_amount' => 13.00,
            'product_service_charge' => 5.00,
            'product_delivery_charge' => 2.00,
            'transaction_uuid' => 'test-uuid-with-tax',
        ];

        $result = $this->epay->createPayment($paymentData);

        $this->assertEqualsWithDelta(100.00, $result['amount'], PHP_FLOAT_EPSILON);
        $this->assertEqualsWithDelta(13.00, $result['tax_amount'], PHP_FLOAT_EPSILON);
        $this->assertEqualsWithDelta(5.00, $result['product_service_charge'], PHP_FLOAT_EPSILON);
        $this->assertEqualsWithDelta(2.00, $result['product_delivery_charge'], PHP_FLOAT_EPSILON);
        $this->assertEqualsWithDelta(120.00, $result['total_amount'], PHP_FLOAT_EPSILON); // 100 + 13 + 5 + 2
    }

    /**
     * @throws EsewaException
     */
    public function testCreatePaymentThrowsExceptionForMissingAmount(): void
    {
        $this->epay->configure($this->validConfiguration);

        $paymentData = [
            'transaction_uuid' => 'test-uuid-12345',
        ];

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Amount is required.');

        $this->epay->createPayment($paymentData);
    }

    /**
     * @throws EsewaException
     */
    public function testCreatePaymentThrowsExceptionForNegativeAmount(): void
    {
        $this->epay->configure($this->validConfiguration);

        $paymentData = [
            'amount' => -50.00,
            'transaction_uuid' => 'test-uuid-12345',
        ];

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Amount must be greater than 0.');

        $this->epay->createPayment($paymentData);
    }

    /**
     * @throws EsewaException
     */
    public function testCreatePaymentThrowsExceptionForZeroAmount(): void
    {
        $this->epay->configure($this->validConfiguration);

        $paymentData = [
            'amount' => 0,
            'transaction_uuid' => 'test-uuid-12345',
        ];

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Amount must be greater than 0.');

        $this->epay->createPayment($paymentData);
    }

    /**
     * @throws EsewaException
     */
    public function testCreatePaymentThrowsExceptionForMissingTransactionUuid(): void
    {
        $this->epay->configure($this->validConfiguration);

        $paymentData = [
            'amount' => 100.00,
        ];

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Transaction UUID is required.');

        $this->epay->createPayment($paymentData);
    }

    /**
     * @throws EsewaException
     */
    public function testCreatePaymentThrowsExceptionForInvalidTransactionUuid(): void
    {
        $this->epay->configure($this->validConfiguration);

        $paymentData = [
            'amount' => 100.00,
            'transaction_uuid' => 'invalid@uuid!',
        ];

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Transaction UUID must be alphanumeric and may contain hyphens only.');

        $this->epay->createPayment($paymentData);
    }

    /**
     * @throws EsewaException
     */
    public function testCreatePaymentGeneratesValidSignature(): void
    {
        $this->epay->configure($this->validConfiguration);

        $paymentData = [
            'amount' => 100.00,
            'transaction_uuid' => 'test-uuid-12345',
        ];

        $result = $this->epay->createPayment($paymentData);

        $this->assertNotEmpty($result['signature']);
        $this->assertEquals('total_amount,transaction_uuid,product_code', $result['signed_field_names']);
    }

    /**
     * @throws EsewaException
     */
    public function testVerifyPaymentWithValidResponse(): void
    {
        $this->epay->configure($this->validConfiguration);

        $responseData = [
            'transaction_code' => 'TXN12345',
            'status' => 'COMPLETE',
            'total_amount' => 100,
            'transaction_uuid' => 'test-uuid-12345',
            'product_code' => 'EPAYTEST',
            'signed_field_names' => 'transaction_code,status,total_amount,transaction_uuid,product_code',
        ];

        // Generate proper signature
        $dataToSign = 'transaction_code=TXN12345,status=COMPLETE,total_amount=100,transaction_uuid=test-uuid-12345,product_code=EPAYTEST';
        $signature = base64_encode(hash_hmac('sha256', $dataToSign, (string) $this->validConfiguration['secret_key'], true));
        $responseData['signature'] = $signature;

        $encodedResponse = base64_encode(json_encode($responseData));

        $result = $this->epay->verifyPayment($encodedResponse);

        $this->assertSame($responseData, $result);
    }

    /**
     * @throws EsewaException
     */
    public function testVerifyPaymentThrowsExceptionForInvalidBase64(): void
    {
        $this->epay->configure($this->validConfiguration);

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Failed to decode response data.');

        $this->epay->verifyPayment('invalid-base64!!!');
    }

    /**
     * @throws EsewaException
     */
    public function testVerifyPaymentThrowsExceptionForMissingSignature(): void
    {
        $this->epay->configure($this->validConfiguration);

        $responseData = [
            'transaction_code' => 'TXN12345',
            'status' => 'COMPLETE',
            'signed_field_names' => 'transaction_code,status',
        ];

        $encodedResponse = base64_encode(json_encode($responseData));

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Invalid response: missing signature or signed fields.');

        $this->epay->verifyPayment($encodedResponse);
    }

    /**
     * @throws EsewaException
     */
    public function testVerifyPaymentThrowsExceptionForInvalidSignature(): void
    {
        $this->epay->configure($this->validConfiguration);

        $responseData = [
            'transaction_code' => 'TXN12345',
            'status' => 'COMPLETE',
            'signed_field_names' => 'transaction_code,status',
            'signature' => 'invalid-signature',
        ];

        $encodedResponse = base64_encode(json_encode($responseData));

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Invalid signature in response.');

        $this->epay->verifyPayment($encodedResponse);
    }

    /**
     * @throws EsewaException
     */
    public function testVerifyPaymentThrowsExceptionForMissingSignedField(): void
    {
        $this->epay->configure($this->validConfiguration);

        $responseData = [
            'transaction_code' => 'TXN12345',
            'signed_field_names' => 'transaction_code,missing_field',
            'signature' => 'some-signature',
        ];

        $encodedResponse = base64_encode(json_encode($responseData));

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Missing signed field: missing_field');

        $this->epay->verifyPayment($encodedResponse);
    }

    /**
     * @throws EsewaException
     */
    public function testCheckStatusWithValidResponse(): void
    {
        $this->epay->configure($this->validConfiguration);

        $responseData = [
            'code' => 0,
            'message' => 'Success',
            'transaction_details' => [
                'status' => 'COMPLETE',
                'total_amount' => 100,
            ],
        ];

        $this->httpClientMock
            ->expects($this->once())
            ->method('get')
            ->with($this->stringContains('api/epay/transaction/status'))
            ->willReturn(json_encode($responseData));

        $result = $this->epay->checkStatus('test-uuid-12345', 100.00);

        $this->assertSame($responseData, $result);
    }

    /**
     * @throws EsewaException
     */
    public function testCheckStatusThrowsExceptionForInvalidJson(): void
    {
        $this->epay->configure($this->validConfiguration);

        $this->httpClientMock
            ->expects($this->once())
            ->method('get')
            ->willReturn('invalid json {');

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessageMatches('/Invalid JSON response:/');

        $this->epay->checkStatus('test-uuid-12345', 100.00);
    }

    /**
     * @throws EsewaException
     */
    public function testCheckStatusThrowsExceptionForApiError(): void
    {
        $this->epay->configure($this->validConfiguration);

        $errorResponse = [
            'code' => 1,
            'message' => 'Transaction not found',
        ];

        $this->httpClientMock
            ->expects($this->once())
            ->method('get')
            ->willReturn(json_encode($errorResponse));

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('API Error: Transaction not found');

        $this->epay->checkStatus('test-uuid-12345', 100.00);
    }

    /**
     * @throws EsewaException
     */
    public function testGetFormActionUrlForTestEnvironment(): void
    {
        $options = $this->validConfiguration;
        $options['environment'] = 'test';
        $this->epay->configure($options);

        $url = $this->epay->getFormActionUrl();

        $this->assertSame('https://rc-epay.esewa.com.np/api/epay/main/v2/form', $url);
    }

    /**
     * @throws EsewaException
     */
    public function testGetFormActionUrlForProductionEnvironment(): void
    {
        $options = $this->validConfiguration;
        $options['environment'] = 'production';
        $this->epay->configure($options);

        $url = $this->epay->getFormActionUrl();

        $this->assertSame('https://epay.esewa.com.np/api/epay/main/v2/form', $url);
    }

    /**
     * @throws EsewaException
     */
    public function testVerifySignatureWithValidData(): void
    {
        $this->epay->configure($this->validConfiguration);

        $data = [
            'transaction_code' => 'TXN12345',
            'status' => 'COMPLETE',
            'signed_field_names' => 'transaction_code,status',
        ];

        $dataToSign = 'transaction_code=TXN12345,status=COMPLETE';
        $signature = base64_encode(hash_hmac('sha256', $dataToSign, (string) $this->validConfiguration['secret_key'], true));

        $isValid = $this->epay->verifySignature($data, $signature);

        $this->assertTrue($isValid);
    }

    /**
     * @throws EsewaException
     */
    public function testVerifySignatureWithInvalidSignature(): void
    {
        $this->epay->configure($this->validConfiguration);

        $data = [
            'transaction_code' => 'TXN12345',
            'status' => 'COMPLETE',
            'signed_field_names' => 'transaction_code,status',
        ];

        $isValid = $this->epay->verifySignature($data, 'invalid-signature');

        $this->assertFalse($isValid);
    }

    /**
     * @throws EsewaException
     */
    public function testVerifySignatureReturnsFalseForMissingSignedFieldNames(): void
    {
        $this->epay->configure($this->validConfiguration);

        $data = [
            'transaction_code' => 'TXN12345',
            'status' => 'COMPLETE',
        ];

        $isValid = $this->epay->verifySignature($data, 'some-signature');

        $this->assertFalse($isValid);
    }

    /**
     * @throws EsewaException
     */
    public function testVerifySignatureReturnsFalseForMissingSignedField(): void
    {
        $this->epay->configure($this->validConfiguration);

        $data = [
            'transaction_code' => 'TXN12345',
            'signed_field_names' => 'transaction_code,missing_field',
        ];

        $isValid = $this->epay->verifySignature($data, 'some-signature');

        $this->assertFalse($isValid);
    }

    /**
     * @throws EsewaException
     */
    public function testCreatePaymentWithStringAmounts(): void
    {
        $this->epay->configure($this->validConfiguration);

        $paymentData = [
            'amount' => '100.50',
            'tax_amount' => '13.50',
            'transaction_uuid' => 'test-uuid-string-amounts',
        ];

        $result = $this->epay->createPayment($paymentData);

        $this->assertEqualsWithDelta(100.50, $result['amount'], PHP_FLOAT_EPSILON);
        $this->assertEqualsWithDelta(13.50, $result['tax_amount'], PHP_FLOAT_EPSILON);
        $this->assertEqualsWithDelta(114.00, $result['total_amount'], PHP_FLOAT_EPSILON);
    }

    /**
     * @throws EsewaException
     */
    public function testCreatePaymentWithVeryLargeAmount(): void
    {
        $this->epay->configure($this->validConfiguration);

        $paymentData = [
            'amount' => 999999.99,
            'transaction_uuid' => 'large-amount-test',
        ];

        $result = $this->epay->createPayment($paymentData);

        $this->assertEqualsWithDelta(999999.99, $result['amount'], PHP_FLOAT_EPSILON);
        $this->assertEqualsWithDelta(999999.99, $result['total_amount'], PHP_FLOAT_EPSILON);
    }

    /**
     * @throws EsewaException
     */
    public function testCreatePaymentWithVerySmallAmount(): void
    {
        $this->epay->configure($this->validConfiguration);

        $paymentData = [
            'amount' => 0.01,
            'transaction_uuid' => 'small-amount-test',
        ];

        $result = $this->epay->createPayment($paymentData);

        $this->assertEqualsWithDelta(0.01, $result['amount'], PHP_FLOAT_EPSILON);
        $this->assertEqualsWithDelta(0.01, $result['total_amount'], PHP_FLOAT_EPSILON);
    }

    /**
     * @throws EsewaException
     */
    public function testSignatureConsistencyAcrossMultipleCalls(): void
    {
        $this->epay->configure($this->validConfiguration);

        $paymentData = [
            'amount' => 100.00,
            'transaction_uuid' => 'consistency-test',
        ];

        $result1 = $this->epay->createPayment($paymentData);
        $result2 = $this->epay->createPayment($paymentData);

        $this->assertEquals($result1['signature'], $result2['signature']);
    }

    /**
     * @throws EsewaException
     */
    public function testSecurityValidationForSqlInjectionInUuid(): void
    {
        $this->epay->configure($this->validConfiguration);

        $paymentData = [
            'amount' => 100.00,
            'transaction_uuid' => "'; DROP TABLE users; --",
        ];

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Transaction UUID must be alphanumeric and may contain hyphens only.');

        $this->epay->createPayment($paymentData);
    }

    /**
     * @throws EsewaException
     */
    public function testSecurityValidationForScriptTagInUuid(): void
    {
        $this->epay->configure($this->validConfiguration);

        $paymentData = [
            'amount' => 100.00,
            'transaction_uuid' => '<script>alert("xss")</script>',
        ];

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Transaction UUID must be alphanumeric and may contain hyphens only.');

        $this->epay->createPayment($paymentData);
    }
}

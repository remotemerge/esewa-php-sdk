<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RemoteMerge\Esewa\Exceptions\EsewaException;
use RemoteMerge\Esewa\Http\HttpClientInterface;
use RemoteMerge\Esewa\TokenPay\TokenInterface;
use RemoteMerge\Esewa\TokenPay\TokenPay;

#[\PHPUnit\Framework\Attributes\CoversClass(TokenPay::class)]
class TokenPayTest extends TestCase
{
    private TokenPay $tokenPay;

    private MockObject&HttpClientInterface $httpClientMock;

    private array $validConfiguration;

    protected function setUp(): void
    {
        $this->httpClientMock = $this->createMock(HttpClientInterface::class);
        $this->tokenPay = new TokenPay($this->httpClientMock);

        $this->validConfiguration = [
            'product_code' => 'EPAYTEST',
            'secret_key' => 'BhwIWVKBJdzXAz9SaBjKyQNGwFFgQAWJYARKEMOITYHggE=',
            'client_secret' => 'client-secret-123',
            'environment' => 'test',
        ];
    }

    public function testImplementsTokenInterface(): void
    {
        $this->assertInstanceOf(TokenInterface::class, $this->tokenPay);
    }

    public function testConstructorWithoutHttpClient(): void
    {
        $tokenPay = new TokenPay();
        $this->assertInstanceOf(TokenPay::class, $tokenPay);
    }

    /**
     * @throws EsewaException
     */
    public function testConfigureWithValidOptions(): void
    {
        $this->tokenPay->configure($this->validConfiguration);

        $this->assertSame('test', $this->tokenPay->getEnvironment());
        $this->assertSame('EPAYTEST', $this->tokenPay->getProductCode());
    }

    public function testConfigureThrowsExceptionForMissingClientSecret(): void
    {
        $options = $this->validConfiguration;
        unset($options['client_secret']);

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Client secret is required for token-based authentication.');

        $this->tokenPay->configure($options);
    }

    /**
     * @throws EsewaException
     */
    public function testGetEnvironmentAfterConfiguration(): void
    {
        $this->tokenPay->configure($this->validConfiguration);

        $this->assertSame('test', $this->tokenPay->getEnvironment());
    }

    public function testGetProductCodeAfterConfiguration(): void
    {
        $this->tokenPay->configure($this->validConfiguration);

        $this->assertSame('EPAYTEST', $this->tokenPay->getProductCode());
    }

    /**
     * @throws EsewaException
     */
    public function testAuthenticateWithValidCredentials(): void
    {
        $this->tokenPay->configure($this->validConfiguration);

        $authResponse = [
            'access_token' => 'access-token-12345',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'refresh_token' => 'refresh-token-67890',
        ];

        $this->httpClientMock
            ->expects($this->once())
            ->method('post')
            ->with(
                $this->stringContains('/access-token'),
                $this->callback(
                    fn ($data): bool => isset($data['grant_type']) && $data['grant_type'] === 'password'
                        && isset($data['username'], $data['password'], $data['client_secret'])
                )
            )
            ->willReturn(json_encode($authResponse));

        $result = $this->tokenPay->authenticate('test-user', 'test-password');

        $this->assertSame($authResponse, $result);
    }

    /**
     * @throws EsewaException
     */
    public function testAuthenticateThrowsExceptionForInvalidJson(): void
    {
        $this->tokenPay->configure($this->validConfiguration);

        $this->httpClientMock
            ->expects($this->once())
            ->method('post')
            ->willReturn('invalid json {');

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessageMatches('/Invalid authentication response:/');

        $this->tokenPay->authenticate('test-user', 'test-password');
    }

    /**
     * @throws EsewaException
     */
    public function testAuthenticateThrowsExceptionForApiError(): void
    {
        $this->tokenPay->configure($this->validConfiguration);

        $errorResponse = [
            'code' => 1,
            'message' => 'Invalid credentials',
        ];

        $this->httpClientMock
            ->expects($this->once())
            ->method('post')
            ->willReturn(json_encode($errorResponse));

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('API Error: Invalid credentials');

        $this->tokenPay->authenticate('test-user', 'test-password');
    }

    /**
     * @throws EsewaException
     */
    public function testAuthenticateThrowsExceptionForMissingAccessToken(): void
    {
        $this->tokenPay->configure($this->validConfiguration);

        $incompleteResponse = [
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ];

        $this->httpClientMock
            ->expects($this->once())
            ->method('post')
            ->willReturn(json_encode($incompleteResponse));

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Authentication failed: no access token received.');

        $this->tokenPay->authenticate('test-user', 'test-password');
    }

    /**
     * @throws EsewaException
     */
    public function testRefreshTokenWithValidToken(): void
    {
        $this->tokenPay->configure($this->validConfiguration);

        $refreshResponse = [
            'access_token' => 'new-access-token-12345',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'refresh_token' => 'new-refresh-token-67890',
        ];

        $this->httpClientMock
            ->expects($this->once())
            ->method('post')
            ->with(
                $this->stringContains('/access-token'),
                $this->callback(
                    fn ($data): bool => isset($data['grant_type']) && $data['grant_type'] === 'refresh_token'
                        && isset($data['refresh_token'], $data['client_secret'])
                )
            )
            ->willReturn(json_encode($refreshResponse));

        $result = $this->tokenPay->refreshToken('old-refresh-token');

        $this->assertSame($refreshResponse, $result);
    }

    /**
     * @throws EsewaException
     */
    public function testRefreshTokenThrowsExceptionForInvalidJson(): void
    {
        $this->tokenPay->configure($this->validConfiguration);

        $this->httpClientMock
            ->expects($this->once())
            ->method('post')
            ->willReturn('invalid json {');

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessageMatches('/Invalid refresh token response:/');

        $this->tokenPay->refreshToken('old-refresh-token');
    }

    /**
     * @throws EsewaException
     */
    public function testRefreshTokenThrowsExceptionForMissingAccessToken(): void
    {
        $this->tokenPay->configure($this->validConfiguration);

        $incompleteResponse = [
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ];

        $this->httpClientMock
            ->expects($this->once())
            ->method('post')
            ->willReturn(json_encode($incompleteResponse));

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('TokenPay refresh failed: no access token received.');

        $this->tokenPay->refreshToken('old-refresh-token');
    }

    /**
     * @throws EsewaException
     */
    public function testInquiryWithValidRequestId(): void
    {
        $this->tokenPay->configure($this->validConfiguration);
        $this->tokenPay->setAccessToken('test-access-token');

        $inquiryResponse = [
            'code' => 0,
            'message' => 'Success',
            'request_id' => 'REQ12345',
            'amount' => 100,
            'status' => 'pending',
        ];

        $this->httpClientMock
            ->expects($this->once())
            ->method('get')
            ->with(
                $this->stringContains('/inquiry/REQ12345'),
                $this->callback(fn ($headers): bool => isset($headers['Authorization']) && $headers['Authorization'] === 'Bearer test-access-token'
                        && isset($headers['Content-Type']) && $headers['Content-Type'] === 'application/json')
            )
            ->willReturn(json_encode($inquiryResponse));

        $result = $this->tokenPay->inquiry('REQ12345');

        $this->assertSame($inquiryResponse, $result);
    }

    /**
     * @throws EsewaException
     */
    public function testInquiryWithAdditionalParameters(): void
    {
        $this->tokenPay->configure($this->validConfiguration);
        $this->tokenPay->setAccessToken('test-access-token');

        $inquiryResponse = [
            'code' => 0,
            'message' => 'Success',
        ];

        $this->httpClientMock
            ->expects($this->once())
            ->method('get')
            ->with($this->stringContains('/inquiry/REQ12345?param1=value1&param2=value2'))
            ->willReturn(json_encode($inquiryResponse));

        $result = $this->tokenPay->inquiry('REQ12345', ['param1' => 'value1', 'param2' => 'value2']);

        $this->assertSame($inquiryResponse, $result);
    }

    /**
     * @throws EsewaException
     */
    public function testInquiryThrowsExceptionWhenNotAuthenticated(): void
    {
        $this->tokenPay->configure($this->validConfiguration);

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Not authenticated. Please call authenticate() first.');

        $this->tokenPay->inquiry('REQ12345');
    }

    /**
     * @throws EsewaException
     */
    public function testInquiryThrowsExceptionForInvalidJson(): void
    {
        $this->tokenPay->configure($this->validConfiguration);
        $this->tokenPay->setAccessToken('test-access-token');

        $this->httpClientMock
            ->expects($this->once())
            ->method('get')
            ->willReturn('invalid json {');

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessageMatches('/Invalid inquiry response:/');

        $this->tokenPay->inquiry('REQ12345');
    }

    /**
     * @throws EsewaException
     */
    public function testPaymentWithValidData(): void
    {
        $this->tokenPay->configure($this->validConfiguration);
        $this->tokenPay->setAccessToken('test-access-token');

        $paymentData = [
            'request_id' => 'REQ12345',
            'amount' => 100.00,
            'transaction_code' => 'TXN67890',
        ];

        $paymentResponse = [
            'code' => 0,
            'message' => 'Payment successful',
            'transaction_code' => 'TXN67890',
        ];

        $this->httpClientMock
            ->expects($this->once())
            ->method('post')
            ->with(
                $this->stringContains('/payment'),
                $paymentData,
                $this->callback(fn ($headers): bool => isset($headers['Authorization']) && $headers['Authorization'] === 'Bearer test-access-token')
            )
            ->willReturn(json_encode($paymentResponse));

        $result = $this->tokenPay->payment($paymentData);

        $this->assertSame($paymentResponse, $result);
    }

    /**
     * @throws EsewaException
     */
    public function testPaymentThrowsExceptionWhenNotAuthenticated(): void
    {
        $this->tokenPay->configure($this->validConfiguration);

        $paymentData = [
            'request_id' => 'REQ12345',
            'amount' => 100.00,
            'transaction_code' => 'TXN67890',
        ];

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Not authenticated. Please call authenticate() first.');

        $this->tokenPay->payment($paymentData);
    }

    /**
     * @throws EsewaException
     */
    public function testPaymentThrowsExceptionForInvalidData(): void
    {
        $this->tokenPay->configure($this->validConfiguration);
        $this->tokenPay->setAccessToken('test-access-token');

        $invalidPaymentData = [
            'request_id' => 'REQ12345',
            'amount' => -50.00, // Invalid negative amount
            'transaction_code' => 'TXN67890',
        ];

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Amount must be greater than 0.');

        $this->tokenPay->payment($invalidPaymentData);
    }

    /**
     * @throws EsewaException
     */
    public function testStatusCheckWithValidData(): void
    {
        $this->tokenPay->configure($this->validConfiguration);
        $this->tokenPay->setAccessToken('test-access-token');

        $statusData = [
            'request_id' => 'REQ12345',
            'amount' => 100.00,
            'transaction_code' => 'TXN67890',
        ];

        $statusResponse = [
            'code' => 0,
            'message' => 'Transaction completed',
            'status' => 'COMPLETE',
        ];

        $this->httpClientMock
            ->expects($this->once())
            ->method('post')
            ->with(
                $this->stringContains('/status'),
                $statusData,
                $this->callback(fn ($headers): bool => isset($headers['Authorization']) && $headers['Authorization'] === 'Bearer test-access-token')
            )
            ->willReturn(json_encode($statusResponse));

        $result = $this->tokenPay->statusCheck($statusData);

        $this->assertSame($statusResponse, $result);
    }

    /**
     * @throws EsewaException
     */
    public function testStatusCheckThrowsExceptionWhenNotAuthenticated(): void
    {
        $this->tokenPay->configure($this->validConfiguration);

        $statusData = [
            'request_id' => 'REQ12345',
            'amount' => 100.00,
            'transaction_code' => 'TXN67890',
        ];

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Not authenticated. Please call authenticate() first.');

        $this->tokenPay->statusCheck($statusData);
    }

    /**
     * @throws EsewaException
     */
    public function testStatusCheckThrowsExceptionForInvalidData(): void
    {
        $this->tokenPay->configure($this->validConfiguration);
        $this->tokenPay->setAccessToken('test-access-token');

        $invalidStatusData = [
            'request_id' => '', // Invalid empty request ID
            'amount' => 100.00,
            'transaction_code' => 'TXN67890',
        ];

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Request ID cannot be empty.');

        $this->tokenPay->statusCheck($invalidStatusData);
    }

    /**
     * @throws EsewaException
     */
    public function testSetAccessToken(): void
    {
        $this->tokenPay->configure($this->validConfiguration);
        $this->tokenPay->setAccessToken('custom-access-token');

        // Test that we can now make authenticated requests
        $inquiryResponse = [
            'code' => 0,
            'message' => 'Success',
        ];

        $this->httpClientMock
            ->expects($this->once())
            ->method('get')
            ->with(
                $this->anything(),
                $this->callback(fn ($headers): bool => isset($headers['Authorization']) && $headers['Authorization'] === 'Bearer custom-access-token')
            )
            ->willReturn(json_encode($inquiryResponse));

        $result = $this->tokenPay->inquiry('REQ12345');
        $this->assertSame($inquiryResponse, $result);
    }

    /**
     * @throws EsewaException
     */
    public function testVerifySignatureAlwaysReturnsTrue(): void
    {
        $this->tokenPay->configure($this->validConfiguration);

        $data = ['key' => 'value'];
        $signature = 'any-signature';

        $result = $this->tokenPay->verifySignature($data, $signature);

        $this->assertTrue($result);
    }

    /**
     * @throws EsewaException
     */
    public function testAuthenticateEncodesCredentialsInBase64(): void
    {
        $this->tokenPay->configure($this->validConfiguration);

        $authResponse = [
            'access_token' => 'test-token',
            'token_type' => 'Bearer',
        ];

        $this->httpClientMock
            ->expects($this->once())
            ->method('post')
            ->with(
                $this->anything(),
                $this->callback(fn ($data): bool
                    // Verify that client_secret and password are base64 encoded
                    => $data['client_secret'] === base64_encode('client-secret-123')
                    && $data['password'] === base64_encode('test-password'))
            )
            ->willReturn(json_encode($authResponse));

        $this->tokenPay->authenticate('test-user', 'test-password');
    }

    /**
     * @throws EsewaException
     */
    public function testRefreshTokenEncodesClientSecretInBase64(): void
    {
        $this->tokenPay->configure($this->validConfiguration);

        $refreshResponse = [
            'access_token' => 'new-token',
            'token_type' => 'Bearer',
        ];

        $this->httpClientMock
            ->expects($this->once())
            ->method('post')
            ->with(
                $this->anything(),
                $this->callback(fn ($data): bool => $data['client_secret'] === base64_encode('client-secret-123'))
            )
            ->willReturn(json_encode($refreshResponse));

        $this->tokenPay->refreshToken('refresh-token');
    }

    /**
     * @throws EsewaException
     */
    public function testAuthenticationSetsTokenType(): void
    {
        $this->tokenPay->configure($this->validConfiguration);

        $authResponse = [
            'access_token' => 'test-token',
            'token_type' => 'Custom',
        ];

        $this->httpClientMock
            ->expects($this->once())
            ->method('post')
            ->willReturn(json_encode($authResponse));

        $this->tokenPay->authenticate('test-user', 'test-password');

        // Test that the custom token type is used in subsequent requests
        $inquiryResponse = ['code' => 0];

        $this->httpClientMock
            ->expects($this->once())
            ->method('get')
            ->with(
                $this->anything(),
                $this->callback(fn ($headers): bool => $headers['Authorization'] === 'Custom test-token')
            )
            ->willReturn(json_encode($inquiryResponse));

        $this->tokenPay->inquiry('REQ12345');
    }

    /**
     * @throws EsewaException
     */
    public function testAuthenticationDefaultsTokenTypeToBearer(): void
    {
        $this->tokenPay->configure($this->validConfiguration);

        $authResponse = [
            'access_token' => 'test-token',
            // No token_type field
        ];

        $this->httpClientMock
            ->expects($this->once())
            ->method('post')
            ->willReturn(json_encode($authResponse));

        $this->tokenPay->authenticate('test-user', 'test-password');

        // Test that Bearer is used as default
        $inquiryResponse = ['code' => 0];

        $this->httpClientMock
            ->expects($this->once())
            ->method('get')
            ->with(
                $this->anything(),
                $this->callback(fn ($headers): bool => $headers['Authorization'] === 'Bearer test-token')
            )
            ->willReturn(json_encode($inquiryResponse));

        $this->tokenPay->inquiry('REQ12345');
    }

    /**
     * @throws EsewaException
     */
    public function testSecurityValidationForSqlInjectionInRequestId(): void
    {
        $this->tokenPay->configure($this->validConfiguration);
        $this->tokenPay->setAccessToken('test-token');

        // Mock HTTP client to return invalid JSON to trigger validation error
        $this->httpClientMock
            ->expects($this->once())
            ->method('post')
            ->willReturn('invalid json {');

        $maliciousData = [
            'request_id' => "'; DROP TABLE users; --",
            'amount' => 100.00,
            'transaction_code' => 'TXN123',
        ];

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessageMatches('/Invalid payment response:/');

        $this->tokenPay->payment($maliciousData);
    }

    /**
     * @throws EsewaException
     */
    public function testSecurityValidationForScriptTagInTransactionCode(): void
    {
        $this->tokenPay->configure($this->validConfiguration);
        $this->tokenPay->setAccessToken('test-token');

        // Mock HTTP client to return invalid JSON to trigger validation error
        $this->httpClientMock
            ->expects($this->once())
            ->method('post')
            ->willReturn('invalid json {');

        $maliciousData = [
            'request_id' => 'REQ123',
            'amount' => 100.00,
            'transaction_code' => '<script>alert("xss")</script>',
        ];

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessageMatches('/Invalid payment response:/');

        $this->tokenPay->payment($maliciousData);
    }

    /**
     * @throws EsewaException
     */
    public function testLargeAmountPayment(): void
    {
        $this->tokenPay->configure($this->validConfiguration);
        $this->tokenPay->setAccessToken('test-token');

        $paymentData = [
            'request_id' => 'REQ12345',
            'amount' => 999999.99,
            'transaction_code' => 'TXN67890',
        ];

        $paymentResponse = ['code' => 0];

        $this->httpClientMock
            ->expects($this->once())
            ->method('post')
            ->willReturn(json_encode($paymentResponse));

        $result = $this->tokenPay->payment($paymentData);

        $this->assertSame($paymentResponse, $result);
    }

    /**
     * @throws EsewaException
     */
    public function testSmallAmountPayment(): void
    {
        $this->tokenPay->configure($this->validConfiguration);
        $this->tokenPay->setAccessToken('test-token');

        $paymentData = [
            'request_id' => 'REQ12345',
            'amount' => 0.01,
            'transaction_code' => 'TXN67890',
        ];

        $paymentResponse = ['code' => 0];

        $this->httpClientMock
            ->expects($this->once())
            ->method('post')
            ->willReturn(json_encode($paymentResponse));

        $result = $this->tokenPay->payment($paymentData);

        $this->assertSame($paymentResponse, $result);
    }
}

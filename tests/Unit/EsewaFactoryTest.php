<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RemoteMerge\Esewa\Epay\EpayInterface;
use RemoteMerge\Esewa\EsewaFactory;
use RemoteMerge\Esewa\Exceptions\EsewaException;
use RemoteMerge\Esewa\Http\HttpClientInterface;
use RemoteMerge\Esewa\TokenPay\TokenInterface;

#[CoversClass(EsewaFactory::class)]
class EsewaFactoryTest extends TestCase
{
    private array $validEpayOptions;

    private array $validTokenPayOptions;

    protected function setUp(): void
    {
        $this->validEpayOptions = [
            'product_code' => 'EPAYTEST',
            'secret_key' => 'BhwIWVKBJdzXAz9SaBjKyQNGwFFgQAWJYARKEMOITYHggE=',
            'success_url' => 'https://example.com/success',
            'failure_url' => 'https://example.com/failure',
            'environment' => 'test',
        ];

        $this->validTokenPayOptions = [
            'product_code' => 'EPAYTEST',
            'secret_key' => 'BhwIWVKBJdzXAz9SaBjKyQNGwFFgQAWJYARKEMOITYHggE=',
            'client_secret' => 'client-secret-123',
            'environment' => 'test',
        ];
    }

    /**
     * @throws EsewaException
     */
    public function testCreateEpayReturnsEpayInterface(): void
    {
        $epay = EsewaFactory::createEpay($this->validEpayOptions);

        $this->assertInstanceOf(EpayInterface::class, $epay);
    }

    /**
     * @throws EsewaException
     */
    public function testCreateEpayWithCustomHttpClient(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $epay = EsewaFactory::createEpay($this->validEpayOptions, $httpClient);

        $this->assertInstanceOf(EpayInterface::class, $epay);
    }

    public function testCreateEpayThrowsExceptionForMissingProductCode(): void
    {
        $options = $this->validEpayOptions;
        unset($options['product_code']);

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Product code is required.');

        EsewaFactory::createEpay($options);
    }

    public function testCreateEpayThrowsExceptionForMissingSecretKey(): void
    {
        $options = $this->validEpayOptions;
        unset($options['secret_key']);

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Secret key is required.');

        EsewaFactory::createEpay($options);
    }

    public function testCreateEpayThrowsExceptionForMissingSuccessUrl(): void
    {
        $options = $this->validEpayOptions;
        unset($options['success_url']);

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Success URL is required.');

        EsewaFactory::createEpay($options);
    }

    public function testCreateEpayThrowsExceptionForMissingFailureUrl(): void
    {
        $options = $this->validEpayOptions;
        unset($options['failure_url']);

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Failure URL is required.');

        EsewaFactory::createEpay($options);
    }

    public function testCreateEpayThrowsExceptionForEmptyProductCode(): void
    {
        $options = $this->validEpayOptions;
        $options['product_code'] = '';

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Product code cannot be empty.');

        EsewaFactory::createEpay($options);
    }

    public function testCreateEpayThrowsExceptionForEmptySecretKey(): void
    {
        $options = $this->validEpayOptions;
        $options['secret_key'] = '';

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Secret key cannot be empty.');

        EsewaFactory::createEpay($options);
    }

    public function testCreateEpayThrowsExceptionForInvalidEnvironment(): void
    {
        $options = $this->validEpayOptions;
        $options['environment'] = 'invalid';

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Environment must be either "test" or "production".');

        EsewaFactory::createEpay($options);
    }

    /**
     * @throws EsewaException
     */
    public function testCreateTokenPayReturnsTokenInterface(): void
    {
        $tokenPay = EsewaFactory::createTokenPay($this->validTokenPayOptions);

        $this->assertInstanceOf(TokenInterface::class, $tokenPay);
    }

    /**
     * @throws EsewaException
     */
    public function testCreateTokenPayWithCustomHttpClient(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $tokenPay = EsewaFactory::createTokenPay($this->validTokenPayOptions, $httpClient);

        $this->assertInstanceOf(TokenInterface::class, $tokenPay);
    }

    public function testCreateTokenPayThrowsExceptionForMissingProductCode(): void
    {
        $options = $this->validTokenPayOptions;
        unset($options['product_code']);

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Product code is required.');

        EsewaFactory::createTokenPay($options);
    }

    public function testCreateTokenPayThrowsExceptionForMissingSecretKey(): void
    {
        $options = $this->validTokenPayOptions;
        unset($options['secret_key']);

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Secret key is required.');

        EsewaFactory::createTokenPay($options);
    }

    public function testCreateTokenPayThrowsExceptionForMissingClientSecret(): void
    {
        $options = $this->validTokenPayOptions;
        unset($options['client_secret']);

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Client secret is required for token-based authentication.');

        EsewaFactory::createTokenPay($options);
    }

    public function testCreateTokenPayThrowsExceptionForEmptyProductCode(): void
    {
        $options = $this->validTokenPayOptions;
        $options['product_code'] = '';

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Product code cannot be empty.');

        EsewaFactory::createTokenPay($options);
    }

    public function testCreateTokenPayThrowsExceptionForEmptySecretKey(): void
    {
        $options = $this->validTokenPayOptions;
        $options['secret_key'] = '';

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Secret key cannot be empty.');

        EsewaFactory::createTokenPay($options);
    }

    public function testCreateTokenPayThrowsExceptionForInvalidEnvironment(): void
    {
        $options = $this->validTokenPayOptions;
        $options['environment'] = 'staging';

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Environment must be either "test" or "production".');

        EsewaFactory::createTokenPay($options);
    }

    /**
     * @throws EsewaException
     */
    public function testCreateTokenPayDefaultsToTestEnvironment(): void
    {
        $options = $this->validTokenPayOptions;
        unset($options['environment']);

        $tokenPay = EsewaFactory::createTokenPay($options);

        $this->assertInstanceOf(TokenInterface::class, $tokenPay);
    }

    public function testCreateEpayDefaultsToTestEnvironment(): void
    {
        $options = $this->validEpayOptions;
        unset($options['environment']);

        $epay = EsewaFactory::createEpay($options);

        $this->assertInstanceOf(EpayInterface::class, $epay);
    }

    /**
     * @throws EsewaException
     */
    public function testCreateEpayWithProductionEnvironment(): void
    {
        $options = $this->validEpayOptions;
        $options['environment'] = 'production';

        $epay = EsewaFactory::createEpay($options);

        $this->assertInstanceOf(EpayInterface::class, $epay);
    }

    /**
     * @throws EsewaException
     */
    public function testCreateTokenPayWithProductionEnvironment(): void
    {
        $options = $this->validTokenPayOptions;
        $options['environment'] = 'production';

        $tokenPay = EsewaFactory::createTokenPay($options);

        $this->assertInstanceOf(TokenInterface::class, $tokenPay);
    }
}

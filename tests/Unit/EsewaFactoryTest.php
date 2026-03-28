<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use RemoteMerge\Esewa\Contracts\EpayInterface;
use RemoteMerge\Esewa\Contracts\HttpClientInterface;
use RemoteMerge\Esewa\EsewaFactory;
use RemoteMerge\Esewa\Exceptions\EsewaException;
use Tests\ParentTestCase;

#[CoversClass(EsewaFactory::class)]
final class EsewaFactoryTest extends ParentTestCase
{
    private array $validEpayOptions;

    protected function setUp(): void
    {
        $this->validEpayOptions = [
            'product_code' => 'EPAYTEST',
            'secret_key' => 'BhwIWVKBJdzXAz9SaBjKyQNGwFFgQAWJYARKEMOITYHggE=',
            'success_url' => 'https://example.com/success',
            'failure_url' => 'https://example.com/failure',
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
        $httpClient = $this->createStub(HttpClientInterface::class);
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
}

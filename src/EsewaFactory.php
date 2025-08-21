<?php

declare(strict_types=1);

namespace RemoteMerge\Esewa;

use RemoteMerge\Esewa\Epay\Epay;
use RemoteMerge\Esewa\Epay\EpayInterface;
use RemoteMerge\Esewa\Exceptions\EsewaException;
use RemoteMerge\Esewa\Http\HttpClientInterface;
use RemoteMerge\Esewa\TokenPay\TokenInterface;
use RemoteMerge\Esewa\TokenPay\TokenPay;

final class EsewaFactory
{
    /**
     * Creates a new instance of the ePay payment class.
     *
     * @param array<string, mixed> $options Configuration options for the ePay instance.
     *        Required options: 'product_code', 'secret_key', 'success_url', 'failure_url'.
     *        Optional options: 'environment' (default: 'test').
     * @param HttpClientInterface|null $httpClient Optional HTTP client implementation.
     * @throws EsewaException If the configuration options are invalid.
     * @return EpayInterface A configured ePay instance.
     */
    public static function createEpay(array $options, ?HttpClientInterface $httpClient = null): EpayInterface
    {
        $epay = new Epay($httpClient);
        $epay->configure($options);

        return $epay;
    }

    /**
     * Creates a new instance of the TokenPay payment class.
     *
     * @param array<string, mixed> $options Configuration options for the TokenPay instance.
     *        Required options: 'product_code', 'secret_key', 'client_secret'.
     *        Optional options: 'environment' (default: 'test').
     * @param HttpClientInterface|null $httpClient Optional HTTP client implementation.
     * @throws EsewaException If the configuration options are invalid.
     * @return TokenInterface A configured TokenPay instance.
     */
    public static function createTokenPay(array $options, ?HttpClientInterface $httpClient = null): TokenInterface
    {
        $token = new TokenPay($httpClient);
        $token->configure($options);

        return $token;
    }
}

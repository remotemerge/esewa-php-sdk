<?php declare(strict_types=1);

namespace Cixware\Esewa;

abstract class Base
{
    protected static bool $isProduction = false;

    protected static string $baseUrl = 'https://uat.esewa.com.np';

    protected static string $successUrl;

    protected static string $failureUrl;

    protected static string $merchantCode = 'epay_payment';

    protected static \GuzzleHttp\Client $client;

    protected function init(array $configs): void
    {
        // set app environment
        if (isset($configs['is_production']) && is_bool($configs['is_production'])) {
            self::$isProduction = $configs['is_production'];
        }

        // set success url
        if (isset($configs['success_url']) && filter_var($configs['success_url'], FILTER_VALIDATE_URL)) {
            self::$successUrl = $configs['success_url'];
        }

        // set failure url
        if (isset($configs['failure_url']) && filter_var($configs['failure_url'], FILTER_VALIDATE_URL)) {
            self::$failureUrl = $configs['failure_url'];
        }

        // production mode
        if (self::$isProduction) {
            // reset base URL
            self::$baseUrl = 'https://esewa.com.np';

            // set merchant code
            if (empty($configs['merchant_code'])) {
                self::$merchantCode = $configs['merchant_code'];
            }
        }

        // init Guzzle client
        self::$client = new \GuzzleHttp\Client([
            'base_uri' => self::$baseUrl,
            'http_errors' => false,
            'headers' => [
                'User-Agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'Accept' => 'application/xml',
            ],
            'allow_redirects' => [
                'protocols' => ['https'],
            ],
        ]);
    }

    // process
    abstract public function process(string $productId, float $amount, float $taxAmount, float $serviceAmount = 0, float $deliveryAmount = 0): void;

    // verification
    abstract public function verify(string $referenceId, string $productId, float $amount): object;
}

<?php

namespace Cixware\Esewa\Helpers;

trait Configure
{
    /**
     * @var bool $isProduction
     */
    protected static $isProduction = false;

    /**
     * @var string $baseUrl
     */
    protected static $baseUrl = 'https://uat.esewa.com.np';

    /**
     * @var string $successUrl
     */
    protected static $successUrl;

    /**
     * @var string $failureUrl
     */
    protected static $failureUrl;

    /**
     * @var string $merchantCode
     */
    protected static $merchantCode = 'epay_payment';

    /**
     * @param array $configs
     */
    private function init(array $configs): void
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
            if (!isset($configs['merchant_code']) || empty($configs['merchant_code'])) {
                self::$merchantCode = $configs['merchant_code'];
            }
        }
    }
}

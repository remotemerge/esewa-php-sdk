<?php

namespace Cixware\Esewa\Helpers;

use Cixware\Esewa\Exception\EsewaException;

trait Configure
{
    /**
     * @var bool $isProduction
     */
    private $isProduction = false;

    /**
     * @param array $configs
     * @throws EsewaException
     */
    private function init(array $configs): void
    {
        // set app environment
        if (isset($configs['is_production']) && $configs['is_production']) {
            $this->isProduction = true;
            putenv('ESEWA_IS_PRODUCTION=true');
        }

        // set success url
        if (isset($configs['success_url']) && filter_var($configs['success_url'], FILTER_VALIDATE_URL)) {
            putenv('ESEWA_SUCCESS_URL=' . $configs['success_url']);
        }

        // set failure url
        if (isset($configs['failure_url']) && filter_var($configs['failure_url'], FILTER_VALIDATE_URL)) {
            putenv('ESEWA_FAILURE_URL=' . $configs['failure_url']);
        }

        // production mode
        if ($this->isProduction) {
            // set base URL
            putenv('ESEWA_BASE_URL=' . getenv('ESEWA_PRODUCTION_URL'));

            // set merchant code
            if (!isset($configs['merchant_code']) || empty($configs['merchant_code'])) {
                throw new EsewaException('The merchant_code field is required in production.');
            }
            putenv('ESEWA_MERCHANT_CODE=' . $configs['merchant_code']);
        }
    }
}

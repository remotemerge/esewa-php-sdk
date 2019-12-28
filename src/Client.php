<?php

namespace Cixware\Esewa;

use Cixware\Esewa\Exception\EsewaException;
use Cixware\Esewa\Helpers\Configure;
use Cixware\Esewa\Payment\Payment;
use Dotenv\Dotenv;

final class Client
{
    use Configure;

    /**
     * @var Payment $payment
     */
    public $payment;

    /**
     * @param array $configs
     * @throws EsewaException
     */
    public function __construct(array $configs)
    {
        // root directory for package
        $rootPath = str_replace('\\', '/', dirname(__DIR__ . '../')) . '/';

        // load default env variables
        $envFile = file_exists($rootPath . '.env') ? '.env' : '.env.default';
        $dotenv = Dotenv::createImmutable($rootPath, $envFile);
        $dotenv->load();

        // init the configs
        $this->init($configs);

        // init the classes
        $this->payment = new Payment;
    }
}

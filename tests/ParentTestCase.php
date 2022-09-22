<?php declare(strict_types=1);

namespace Tests;

use Cixware\Esewa\Client;
use Cixware\Esewa\Config;
use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;

class ParentTestCase extends TestCase
{
    protected Client $esewa;

    protected function setUp(): void
    {
        parent::setUp();

        // default timezone
        date_default_timezone_set('UTC');

        // load env
        $dotenv = Dotenv::createImmutable(dirname(__DIR__));
        $dotenv->load();

        // required fields
        $dotenv->required(['ESEWA_REFERENCE_ID', 'ESEWA_PRODUCT_ID', 'ESEWA_PAID_AMOUNT']);

        // format params
        $demoUrl = 'http://localhost:8090/demo/';
        $successUrl = $demoUrl . 'success.php';
        $failureUrl = $demoUrl . 'failed.php';

        $config = new Config($successUrl, $failureUrl);
        $this->esewa = new Client($config);
    }
}

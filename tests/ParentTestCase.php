<?php declare(strict_types=1);

namespace Tests;

use Cixware\Esewa\Client;
use Cixware\Esewa\Config;
use PHPUnit\Framework\TestCase;

class ParentTestCase extends TestCase
{
    protected Client $esewa;

    protected function setUp(): void
    {
        parent::setUp();

        // default timezone
        date_default_timezone_set('UTC');

        // format params
        $demoUrl = 'http://localhost:8090/demo/';
        $successUrl = $demoUrl . 'success.php';
        $failureUrl = $demoUrl . 'failed.php';

        $config = new Config($successUrl, $failureUrl);
        $this->esewa = new Client($config);
    }
}

<?php

declare(strict_types=1);

namespace Tests;

use Cixware\Esewa\Client;
use Cixware\Esewa\Config;
use PHPUnit\Framework\TestCase;

class ParentTestCase extends TestCase
{
    protected Client $esewa;

    /**
     * @var string
     */
    private const DEMO_URL = 'http://localhost:8090/demo/';

    protected function setUp(): void
    {
        parent::setUp();

        // default timezone
        date_default_timezone_set('UTC');

        // format urls
        $successUrl = self::DEMO_URL . 'success.php';
        $failureUrl = self::DEMO_URL . 'failed.php';

        $config = new Config($successUrl, $failureUrl);
        $this->esewa = new Client($config);
    }
}

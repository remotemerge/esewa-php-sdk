<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use RemoteMerge\Esewa\Client;

class ParentTestCase extends TestCase
{
    protected Client $esewa;

    /**
     * @var string
     */
    private const DEMO_URL = 'http://localhost:8080/demo/';

    protected function setUp(): void
    {
        parent::setUp();

        // default timezone
        date_default_timezone_set('UTC');

        // format urls
        $successUrl = self::DEMO_URL . 'success.php';
        $failureUrl = self::DEMO_URL . 'failed.php';

        $this->esewa = new Client([
            'merchant_code' => 'EPAYTEST',
            'success_url' => $successUrl,
            'failure_url' => $failureUrl,
        ]);
    }
}

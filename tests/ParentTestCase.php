<?php

namespace Tests;

use Cixware\Esewa\Client;
use Cixware\Esewa\Exception\EsewaException;
use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;

class ParentTestCase extends TestCase
{
    /**
     * @var string $baseUrl
     */
    private static $baseUrl = 'http://localhost:8090/demo/';

    /**
     * @var Client $client
     */
    protected $client;

    /**
     * @throws EsewaException
     */
    protected function setUp(): void
    {
        parent::setUp();

        // default timezone
        date_default_timezone_set('UTC');

        // load env
        $dotenv = Dotenv::createImmutable(dirname(__DIR__));
        $dotenv->load();

        // init client
        $this->client = new Client([
            'success_url' => self::$baseUrl . 'success.php',
            'failure_url' => self::$baseUrl . 'failed.php',
        ]);
    }
}

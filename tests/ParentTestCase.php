<?php

namespace Tests;

use Cixware\Esewa\Client;
use Cixware\Esewa\Exception\EsewaException;
use PHPUnit\Framework\TestCase;

// disable errors
error_reporting(-1);

// default timezone
date_default_timezone_set('UTC');

class ParentTestCase extends TestCase
{
    /**
     * @var string $baseUrl
     */
    private $baseUrl = 'http://localhost:8090/demo/';

    /**
     * @var Client $client
     */
    protected $client;

    public function __construct()
    {
        try {
            $this->client = new Client([
                'success_url' => $this->baseUrl . 'success.php',
                'failure_url' => $this->baseUrl . 'failed.php',
            ]);
        } catch (EsewaException $e) {
            exit($e->getCode() . ' -> ' . $e->getMessage());
        }
        parent::__construct();
    }
}

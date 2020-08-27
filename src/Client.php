<?php

namespace Cixware\Esewa;

use Cixware\Esewa\Helpers\Configure;

final class Client
{
    use Configure;

    /**
     * @var \GuzzleHttp\Client
     */
    protected static $client;

    /**
     * @param array $configs
     */
    public function __construct(array $configs)
    {
        // init configs
        $this->init($configs);

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
}

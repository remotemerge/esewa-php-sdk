<?php
// init autoloader
require dirname(__DIR__) . '/vendor/autoload.php';

use Cixware\Esewa\Client;

$baseUrl = 'http://localhost:8090/demo/';

try {
    $esewa = new Client([
        'is_production' => false,
        'success_url' => $baseUrl . 'success.php',
        'failure_url' => $baseUrl . 'failed.php',
    ]);

    $hash = hash('SHA256', time());
    $esewa->payment->create(substr($hash, 0, 16), 100, 10);
} catch (Exception $e) {
    exit($e->getCode() . ' -> ' . $e->getMessage());
}

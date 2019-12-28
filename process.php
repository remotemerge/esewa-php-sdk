<?php
// include the composer autoloader
require __DIR__ . './vendor/autoload.php';

use Cixware\Esewa\Client;

$baseUrl = 'http://localhost:8090/';

$esewa = new Client([
    'success_url' => $baseUrl . 'success.php',
    'failure_url' => $baseUrl . 'failed.php',
]);

$hash = hash('SHA256', time());
$esewa->payment->create(substr($hash, 0, 16), 100, 10);

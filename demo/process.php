<?php

declare(strict_types=1);

use RemoteMerge\Esewa\Client;

// Init the autoloader
require dirname(__DIR__) . '/vendor/autoload.php';

// Set up the configuration object
$successUrl = 'http://localhost:8090/demo/success.php';
$failureUrl = 'http://localhost:8090/demo/failed.php';

// Initialize the client with the configuration
$esewa = new Client([
    'merchant_code' => 'EPAYTEST',
    'success_url' => $successUrl,
    'failure_url' => $failureUrl,
]);

// Generate random 16 characters product ID
$productId = substr(bin2hex(random_bytes(8)), 0, 16);

// Process the payment
$esewa->payment($productId, 100, 10);

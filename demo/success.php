<?php

declare(strict_types=1);

use RemoteMerge\Esewa\Client;
use RemoteMerge\Esewa\Config;

// Require the autoloader
require dirname(__DIR__) . '/vendor/autoload.php';

// Set up the configuration object
$successUrl = 'http://localhost:8090/demo/success.php';
$failureUrl = 'http://localhost:8090/demo/failed.php';
$config = new Config($successUrl, $failureUrl);

// Initialize the client
$esewa = new Client($config);

// Get the query parameters
$productId = $_GET['oid'] ?? null;
$referenceId = $_GET['refId'] ?? null;
$amount = $_GET['amt'] ?? null;

try {
    // Verify the payment and output the result
    $status = $esewa->verify($referenceId, $productId, (float) $amount);
    exit($status ? 'The payment is verified.' : 'The payment is not verified.');
} catch (Exception $exception) {
    exit($exception->getMessage());
}

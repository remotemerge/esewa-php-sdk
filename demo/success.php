<?php

declare(strict_types=1);

use RemoteMerge\Esewa\Client;

// Require the autoloader
require dirname(__DIR__) . '/vendor/autoload.php';

// Set up the configuration object
$successUrl = 'http://localhost:8090/demo/success.php';
$failureUrl = 'http://localhost:8090/demo/failed.php';

// Initialize the client
$esewa = new Client([
    'merchant_code' => 'EPAYTEST',
    'success_url' => $successUrl,
    'failure_url' => $failureUrl,
]);

// Get the query parameters
$productId = $_GET['oid'] ?? null;
$referenceId = $_GET['refId'] ?? null;
$amount = $_GET['amt'] ?? null;

try {
    // Verify the payment and output the result
    $status = $esewa->verifyPayment($referenceId, $productId, (float) $amount);
    exit($status ? 'The payment is verified.' : 'The payment is not verified.');
} catch (Exception $exception) {
    exit($exception->getMessage());
}

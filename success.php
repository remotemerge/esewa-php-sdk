<?php
// include the composer autoloader
require __DIR__ . '/vendor/autoload.php';

use Cixware\Esewa\Client;

$baseUrl = 'http://localhost:8090/';

$esewa = new Client([
    'success_url' => $baseUrl . 'success.php',
    'failure_url' => $baseUrl . 'failed.php',
]);

// placeholder fields
$productId = $referenceId = $amount = null;

// check reference field
if (isset($_GET['refId'])) {
    $referenceId = $_GET['refId'];
}

// check product field
if (isset($_GET['oid'])) {
    $productId = $_GET['oid'];
}

// check amount field
if (isset($_GET['amt'])) {
    $amount = $_GET['amt'];
}

if ($referenceId !== null || $productId !== null || $amount !== null) {
    $status = $esewa->payment->verify($referenceId, $productId, $amount);
    if (isset($status->verified) && $status->verified) {
        print_r($status);
        exit('<h1>The payment is verified.</h1>');
    }
}
exit('<h1>The payment is not verified.</h1>');

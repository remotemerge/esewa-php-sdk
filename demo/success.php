<?php declare(strict_types=1);

// init autoloader
require dirname(__DIR__) . '/vendor/autoload.php';

use Cixware\Esewa\Client;
use Cixware\Esewa\Config;
use GuzzleHttp\Exception\GuzzleException;

// format params
$demoUrl = 'http://localhost:8090/demo/';
$successUrl = $demoUrl . 'success.php';
$failureUrl = $demoUrl . 'failed.php';

$config = new Config($successUrl, $failureUrl);
$esewa = new Client($config);

// placeholder fields
$productId = $_GET['oid'] ?? null;
$referenceId = $_GET['refId'] ?? null;
$amount = $_GET['amt'] ?? null;

try {
    $status = $esewa->verify($referenceId, $productId, (float)$amount);
    if ($status) {
        exit('The payment is verified.');
    }

    exit('The payment is not verified.');
} catch (GuzzleException|JsonException $e) {
    exit($e->getMessage());
}

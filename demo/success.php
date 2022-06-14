<?php declare(strict_types=1);

// init autoloader
require dirname(__DIR__) . '/vendor/autoload.php';

use Cixware\Esewa\Client;

try {
    $esewa = new Client([
        'is_production' => false,
    ]);
} catch (Exception $exception) {
    exit($exception->getCode() . ' -> ' . $exception->getMessage());
}

// placeholder fields
$productId = $_GET['oid'] ?? null;
$referenceId = $_GET['refId'] ?? null;
$amount = $_GET['amt'] ?? null;

if ($referenceId !== null || $productId !== null || $amount !== null) {
    $status = $esewa->verify($referenceId, $productId, (float)$amount);
    if (isset($status->verified) && $status->verified) {
        dd($status, 'The payment is verified.');
    }
} else {
    dd('The payment is not verified.');
}

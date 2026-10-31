<?php

declare(strict_types=1);

// Runs against a registry install, so the autoloader is in the working directory;
// __DIR__ would point at this checkout instead.
require_once getcwd() . '/vendor/autoload.php';

use RemoteMerge\Esewa\EsewaFactory;

// eSewa UAT credentials. The reference signature is computed here because the
// example signature published in the eSewa docs does not match its own inputs.
$secretKey = '8gBm/:&EnhH.1/q';
$expected = base64_encode(hash_hmac(
    'sha256',
    'total_amount=100,transaction_uuid=11-201-13,product_code=EPAYTEST',
    $secretKey,
    true,
));

$epay = EsewaFactory::createEpay([
    'product_code' => 'EPAYTEST',
    'secret_key' => $secretKey,
    'success_url' => 'https://example.com/success',
    'failure_url' => 'https://example.com/failure',
]);

$payment = $epay->createPayment(['amount' => 100, 'transaction_uuid' => '11-201-13']);

if ($payment['signature'] !== $expected) {
    fwrite(STDERR, sprintf("Smoke test failed: expected %s, got %s\n", $expected, $payment['signature']));
    exit(1);
}

if (!$epay->verifySignature($payment, $payment['signature'])) {
    fwrite(STDERR, "Smoke test failed: the generated signature did not verify.\n");
    exit(1);
}

echo "Smoke test passed: ePay signature verified.\n";

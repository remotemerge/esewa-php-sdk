<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use RemoteMerge\Esewa\EsewaFactory;
use RemoteMerge\Esewa\Exceptions\EsewaException;

session_start();

$error = null;
$payment = null;

$encodedResponse = $_GET['data'] ?? null;

if ($encodedResponse === null) {
    $error = 'No payment response received.';
} else {
    try {
        $epay = EsewaFactory::createEpay([
            'environment' => 'test',
            'product_code' => 'EPAYTEST',
            'secret_key' => '8gBm/:&EnhH.1/q',
            'success_url' => 'http://localhost:8080/success.php',
            'failure_url' => 'http://localhost:8080/failed.php',
        ]);

        $payment = $epay->verifyPayment($encodedResponse);
    } catch (EsewaException $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Payment <?= $error === null ? 'Successful' : 'Failed' ?> - TechStore</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen">
<div class="bg-white rounded-2xl shadow-lg p-10 max-w-md w-full text-center">
    <?php if ($error === null && $payment !== null): ?>
        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Payment Successful</h1>
        <p class="text-gray-500 mb-6">Your transaction has been verified.</p>
        <div class="bg-gray-50 rounded-xl p-5 text-left space-y-3 mb-6">
            <div class="flex justify-between">
                <span class="text-gray-500">Transaction Code</span>
                <span class="font-semibold"><?= htmlspecialchars((string) ($payment['transaction_code'] ?? '-')) ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Status</span>
                <span class="font-semibold text-green-600"><?= htmlspecialchars((string) ($payment['status'] ?? '-')) ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Total Amount</span>
                <span class="font-semibold">NPR <?= htmlspecialchars((string) ($payment['total_amount'] ?? '-')) ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Transaction UUID</span>
                <span class="font-semibold text-xs"><?= htmlspecialchars((string) ($payment['transaction_uuid'] ?? '-')) ?></span>
            </div>
        </div>
    <?php else: ?>
        <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Verification Failed</h1>
        <p class="text-gray-500 mb-6"><?= htmlspecialchars((string) $error) ?></p>
    <?php endif; ?>
    <a href="/" class="inline-block bg-blue-600 text-white px-8 py-3 rounded-xl hover:bg-blue-700 transition-colors font-semibold">
        Back to Store
    </a>
</div>
</body>
</html>

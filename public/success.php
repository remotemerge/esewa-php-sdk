<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use RemoteMerge\Esewa\EsewaFactory;
use RemoteMerge\Esewa\Exceptions\EsewaException;

session_start();

/**
 * Converts an eSewa amount such as "1,000.0" or 1000 into paisa, so amounts compare exactly instead of as floats.
 */
function toPaisa(mixed $amount): ?int
{
    if (!is_int($amount) && !is_float($amount) && !is_string($amount)) {
        return null;
    }

    $normalized = str_replace(',', '', (string) $amount);

    return is_numeric($normalized) ? (int) round((float) $normalized * 100) : null;
}

$error = null;
$payment = null;
$status = null;
$alreadyConfirmed = false;

$encodedResponse = $_GET['data'] ?? null;
$expectedUuid = $_SESSION['transaction_uuid'] ?? null;
$expectedTotal = $_SESSION['total_amount'] ?? null;

if ($encodedResponse === null) {
    $error = 'No payment response received.';
} elseif ($expectedUuid === null || $expectedTotal === null) {
    $error = 'No pending order found for this payment.';
} else {
    try {
        $epay = EsewaFactory::createEpay([
            'environment' => 'test',
            'product_code' => 'EPAYTEST',
            'secret_key' => '8gBm/:&EnhH.1/q',
            'success_url' => 'http://localhost:8080/success.php',
            'failure_url' => 'http://localhost:8080/failed.php',
        ]);

        // 1. Authenticate the callback. This alone does not prove the payment completed.
        $payment = $epay->verifyPayment($encodedResponse);

        // 2. Match the callback against this store's own order record.
        if ($payment['transaction_uuid'] !== $expectedUuid || toPaisa($payment['total_amount']) !== toPaisa($expectedTotal)) {
            $error = 'Payment details do not match this order.';
        } else {
            // 3. Confirm with eSewa using the order's own UUID and total.
            $statusResponse = $epay->checkStatus($expectedUuid, (float) $expectedTotal);
            $status = is_string($statusResponse['status'] ?? null) ? $statusResponse['status'] : 'UNKNOWN';

            // 4. Only COMPLETE counts as paid. A real store marks the order paid in one atomic, idempotent
            // database update. This session flag only shows that a page refresh must not fulfil the order twice.
            if ($status === 'COMPLETE') {
                $alreadyConfirmed = isset($_SESSION['paid_orders'][$expectedUuid]);
                $_SESSION['paid_orders'][$expectedUuid] = true;
            }
        }
    } catch (EsewaException $e) {
        $error = $e->getMessage();
    }
}

$confirmed = $error === null && $status === 'COMPLETE';
$title = $confirmed ? 'Payment Successful' : ($error === null ? 'Payment Pending' : 'Verification Failed');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title><?= $title ?> - TechStore</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen">
<div class="bg-white rounded-2xl shadow-lg p-10 max-w-md w-full text-center">
    <?php if ($error === null && $payment !== null): ?>
        <?php if ($confirmed): ?>
            <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2"><?= $title ?></h1>
            <p class="text-gray-500 mb-6">
                <?= $alreadyConfirmed ? 'This order was already confirmed.' : 'eSewa confirmed your payment.' ?>
            </p>
        <?php else: ?>
            <div class="w-20 h-20 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01"/>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2"><?= $title ?></h1>
            <p class="text-gray-500 mb-6">eSewa has not confirmed this payment yet. The order stays unpaid until it does.</p>
        <?php endif; ?>
        <div class="bg-gray-50 rounded-xl p-5 text-left space-y-3 mb-6">
            <div class="flex justify-between">
                <span class="text-gray-500">Transaction Code</span>
                <span class="font-semibold"><?= htmlspecialchars((string) $payment['transaction_code']) ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Confirmed Status</span>
                <span class="font-semibold <?= $confirmed ? 'text-green-600' : 'text-yellow-600' ?>"><?= htmlspecialchars((string) $status) ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Total Amount</span>
                <span class="font-semibold">NPR <?= htmlspecialchars((string) $payment['total_amount']) ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Transaction UUID</span>
                <span class="font-semibold text-xs"><?= htmlspecialchars((string) $payment['transaction_uuid']) ?></span>
            </div>
        </div>
    <?php else: ?>
        <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2"><?= $title ?></h1>
        <p class="text-gray-500 mb-6"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <a href="/" class="inline-block bg-blue-600 text-white px-8 py-3 rounded-xl hover:bg-blue-700 transition-colors font-semibold">
        Back to Store
    </a>
</div>
</body>
</html>

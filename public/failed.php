<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

session_start();

$params = $_REQUEST;
if ($params !== []) {
    file_put_contents(__DIR__ . '/failed.log', print_r($params, true) . PHP_EOL, FILE_APPEND);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Payment Failed - TechStore</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen">
<div class="bg-white rounded-2xl shadow-lg p-10 max-w-md w-full text-center">
    <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
        <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </div>
    <h1 class="text-3xl font-bold text-gray-900 mb-2">Payment Failed</h1>
    <p class="text-gray-500 mb-8">Your payment could not be completed. Please try again.</p>
    <a href="/" class="inline-block bg-blue-600 text-white px-8 py-3 rounded-xl hover:bg-blue-700 transition-colors font-semibold">
        Back to Store
    </a>
</div>
</body>
</html>

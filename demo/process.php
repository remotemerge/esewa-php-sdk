<?php

declare(strict_types=1);

use RemoteMerge\Esewa\Client;
use RemoteMerge\Esewa\Config;

// Init the autoloader
require dirname(__DIR__) . '/vendor/autoload.php';

// Set up the configuration object
$successUrl = 'http://localhost:8090/demo/success.php';
$failureUrl = 'http://localhost:8090/demo/failed.php';
$config = new Config($successUrl, $failureUrl);

// Initialize the client
$esewa = new Client($config);

// Generate random 16 characters product ID
$productId = substr(bin2hex(random_bytes(8)), 0, 16);

// Process the payment
$esewa->process($productId, 100, 10);

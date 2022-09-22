<?php declare(strict_types=1);

// init autoloader
require dirname(__DIR__) . '/vendor/autoload.php';

// format params
$successUrl = 'http://localhost:8090/demo/success.php';
$failureUrl = 'http://localhost:8090/demo/failed.php';

$config = new \Cixware\Esewa\Config($successUrl, $failureUrl);

try {
    $esewa = new \Cixware\Esewa\Client($config);

    // generate random product ID
    $productId = hash('SHA256', bin2hex(random_bytes(8)));
    $esewa->process(substr($productId, 0, 16), 100, 10);

} catch (Exception $exception) {
    exit($exception->getMessage());
}

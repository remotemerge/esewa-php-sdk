<?php
// init autoloader
require dirname(__DIR__) . '/vendor/autoload.php';

use Cixware\Esewa\Client;

$schema = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$demoUrl = $schema . '://' . $_SERVER['HTTP_HOST'];

try {
    $esewa = new Client([
        'is_production' => false,
        'success_url' => $demoUrl . '/demo/success.php',
        'failure_url' => $demoUrl . '/demo/failed.php',
    ]);

    $hash = hash('SHA256', time());
    $esewa->process(substr($hash, 0, 16), 100, 10);
} catch (Exception $e) {
    exit($e->getCode() . ' -> ' . $e->getMessage());
}

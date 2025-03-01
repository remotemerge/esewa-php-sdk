<?php

declare(strict_types=1);

// init autoloader
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Print response
$params = $_REQUEST;
foreach ($params as $k => $v) {
    printf("%s: %s\n", $k, $v);
}

<?php

declare(strict_types=1);

// init autoloader
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Print response
$params = $_REQUEST;
if ($params && is_array($params)) {
    file_put_contents('failed.log', $params, FILE_APPEND);
}

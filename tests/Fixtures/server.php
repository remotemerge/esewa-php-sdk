<?php

declare(strict_types=1);

/**
 * Minimal test HTTP server for HttpClient integration tests.
 * Started via: php -S 127.0.0.1:{port} tests/Fixtures/server.php
 */

$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

switch ($uri) {
    case '/ok':
        header('Content-Type: application/json');
        echo '{"success":true}';
        break;

    case '/error-404':
        http_response_code(404);
        echo 'Not Found';
        break;

    case '/error-500':
        http_response_code(500);
        echo 'Server Error';
        break;

    case '/echo':
        header('Content-Type: text/plain');
        echo file_get_contents('php://input');
        break;

    case '/headers':
        header('Content-Type: application/json');
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with((string) $key, 'HTTP_')) {
                $name = str_replace('_', '-', substr((string) $key, 5));
                $headers[$name] = $value;
            }
        }

        echo json_encode($headers);
        break;

    default:
        http_response_code(404);
        echo 'Unknown route';
        break;
}

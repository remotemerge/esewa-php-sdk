<?php

declare(strict_types=1);

namespace RemoteMerge\Esewa\Http;

use RemoteMerge\Esewa\Exceptions\EsewaException;

interface HttpClientInterface
{
    /**
     * Makes a GET request.
     *
     * @param string $url The URL to request.
     * @param array<string, string> $headers Optional headers.
     * @return string The response body.
     * @throws EsewaException If the request fails.
     */
    public function get(string $url, array $headers = []): string;

    /**
     * Makes a POST request.
     *
     * @param string $url The URL to request.
     * @param array<string, mixed> $data The data to send.
     * @param array<string, string> $headers Optional headers.
     * @return string The response body.
     * @throws EsewaException If the request fails.
     */
    public function post(string $url, array $data, array $headers = []): string;
}

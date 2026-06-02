<?php

declare(strict_types=1);

namespace RemoteMerge\Esewa\Http;

use CurlHandle;
use RemoteMerge\Esewa\Contracts\HttpClientInterface;
use RemoteMerge\Esewa\Exceptions\EsewaException;

final class HttpClient implements HttpClientInterface
{
    /**
     * {@inheritDoc}
     * @throws EsewaException
     */
    public function get(string $url, array $headers = []): string
    {
        $ch = curl_init($url);

        $this->setDefaultOptions($ch);

        if ($headers !== []) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->formatHeaders($headers));
        }

        return $this->execute($ch);
    }

    /**
     * {@inheritDoc}
     * @throws EsewaException
     */
    public function post(string $url, array $data, array $headers = []): string
    {
        $ch = curl_init($url);

        $isJson = isset($headers['Content-Type']) && $headers['Content-Type'] === 'application/json';
        $postData = $isJson ? json_encode($data) : http_build_query($data);

        $this->setDefaultOptions($ch);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

        if (!$isJson && !isset($headers['Content-Type'])) {
            $headers['Content-Type'] = 'application/x-www-form-urlencoded';
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->formatHeaders($headers));

        return $this->execute($ch);
    }

    /**
     * Sets the cURL options shared by every request.
     *
     * @param CurlHandle $curlHandle The cURL handle to configure.
     */
    private function setDefaultOptions(CurlHandle $curlHandle): void
    {
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHandle, CURLOPT_TIMEOUT, 30);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 2);
    }

    /**
     * Formats an associative header map into the "Key: Value" list cURL expects.
     *
     * @param array<string, string> $headers The headers to format.
     * @return array<int, string> The formatted header lines.
     */
    private function formatHeaders(array $headers): array
    {
        $headerArray = [];
        foreach ($headers as $key => $value) {
            $headerArray[] = sprintf('%s: %s', $key, $value);
        }

        return $headerArray;
    }

    /**
     * Executes the request, handling errors and closing the handle.
     *
     * @param CurlHandle $curlHandle The configured cURL handle.
     * @throws EsewaException If the request fails or returns an error status.
     * @return string The response body.
     */
    private function execute(CurlHandle $curlHandle): string
    {
        $response = curl_exec($curlHandle);
        $error = curl_error($curlHandle);
        $statusCode = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
        curl_close($curlHandle);

        if ($response === false) {
            throw new EsewaException('cURL Error: ' . $error, 0);
        }

        if ($statusCode >= 400) {
            throw new EsewaException('HTTP Error: ' . $statusCode, $statusCode);
        }

        return $response;
    }
}

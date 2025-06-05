<?php

declare(strict_types=1);

namespace RemoteMerge\Esewa\Http;

use RemoteMerge\Esewa\Exceptions\HttpException;

class CurlHttpClient implements HttpClientInterface
{
    /**
     * {@inheritdoc}
     */
    public function get(string $url, array $headers = []): string
    {
        $ch = curl_init($url);
        if ($ch === false) {
            throw new HttpException('Failed to initialize cURL', 0);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        if (!empty($headers)) {
            $headerArray = [];
            foreach ($headers as $key => $value) {
                $headerArray[] = "$key: $value";
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArray);
        }

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            throw new HttpException('cURL Error: ' . $error, 0);
        }

        if ($statusCode >= 400) {
            throw new HttpException('HTTP Error: ' . $statusCode, $statusCode);
        }

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function post(string $url, array $data, array $headers = []): string
    {
        $ch = curl_init($url);
        if ($ch === false) {
            throw new HttpException('Failed to initialize cURL', 0);
        }

        $isJson = isset($headers['Content-Type']) && $headers['Content-Type'] === 'application/json';
        $postData = $isJson ? json_encode($data) : http_build_query($data);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        if (!$isJson && !isset($headers['Content-Type'])) {
            $headers['Content-Type'] = 'application/x-www-form-urlencoded';
        }

        $headerArray = [];
        foreach ($headers as $key => $value) {
            $headerArray[] = "$key: $value";
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArray);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            throw new HttpException('cURL Error: ' . $error, 0);
        }

        if ($statusCode >= 400) {
            throw new HttpException('HTTP Error: ' . $statusCode, $statusCode);
        }

        return $response;
    }
}

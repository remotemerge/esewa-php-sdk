<?php

declare(strict_types=1);

namespace RemoteMerge\Esewa\TokenPay;

use RemoteMerge\Esewa\AbstractPayment;
use RemoteMerge\Esewa\Exceptions\EsewaException;
use RemoteMerge\Esewa\Http\HttpClient;
use RemoteMerge\Esewa\Http\HttpClientInterface;

final class TokenPay extends AbstractPayment implements TokenInterface
{
    /**
     * The client secret for API authentication.
     */
    private string $clientSecret;

    /**
     * The current access token.
     */
    private ?string $accessToken = null;

    /**
     * The token type (usually "Bearer").
     */
    private string $tokenType = 'Bearer';

    /**
     * API endpoints.
     */
    private const ENDPOINTS = [
        'auth' => '/access-token',
        'inquiry' => '/inquiry',
        'payment' => '/payment',
        'status' => '/status',
    ];

    public function __construct(private readonly ?HttpClientInterface $httpClient = new HttpClient())
    {
        //
    }

    /**
     * {@inheritDoc}
     * @throws EsewaException
     */
    public function configure(array $options): void
    {
        $this->validateCommonConfiguration($options);

        if (!isset($options['client_secret'])) {
            throw new EsewaException('Client secret is required for token-based authentication.');
        }

        $this->clientSecret = $options['client_secret'];
    }

    /**
     * {@inheritDoc}
     */
    public function getEnvironment(): string
    {
        return $this->environment;
    }

    /**
     * {@inheritDoc}
     */
    public function getProductCode(): string
    {
        return $this->productCode;
    }

    /**
     * {@inheritDoc}
     * @throws EsewaException
     */
    public function authenticate(string $username, string $password): array
    {
        $url = $this->getBaseUrl('token') . self::ENDPOINTS['auth'];

        $data = [
            'grant_type' => 'password',
            'client_secret' => base64_encode($this->clientSecret),
            'username' => $username,
            'password' => base64_encode($password),
        ];

        $response = $this->httpClient->post($url, $data);
        $authData = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new EsewaException('Invalid authentication response: ' . json_last_error_msg());
        }

        $this->validateResponse($authData);

        if (!isset($authData['access_token'])) {
            throw new EsewaException('Authentication failed: no access token received.');
        }

        $this->accessToken = $authData['access_token'];
        $this->tokenType = $authData['token_type'] ?? 'Bearer';

        return $authData;
    }

    /**
     * {@inheritDoc}
     * @throws EsewaException
     */
    public function refreshToken(string $refreshToken): array
    {
        $url = $this->getBaseUrl('token') . self::ENDPOINTS['auth'];

        $data = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_secret' => base64_encode($this->clientSecret),
        ];

        $response = $this->httpClient->post($url, $data);
        $authData = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new EsewaException('Invalid refresh token response: ' . json_last_error_msg());
        }

        $this->validateResponse($authData);

        if (!isset($authData['access_token'])) {
            throw new EsewaException('TokenPay refresh failed: no access token received.');
        }

        $this->accessToken = $authData['access_token'];
        $this->tokenType = $authData['token_type'] ?? 'Bearer';

        return $authData;
    }

    /**
     * {@inheritDoc}
     * @throws EsewaException
     */
    public function inquiry(string $requestId, array $additionalParams = []): array
    {
        $this->ensureAuthenticated();

        $url = $this->getBaseUrl('token') . self::ENDPOINTS['inquiry'] . '/' . $requestId;

        if ($additionalParams !== []) {
            $url .= '?' . http_build_query($additionalParams);
        }

        $headers = $this->getAuthHeaders();
        $response = $this->httpClient->get($url, $headers);

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new EsewaException('Invalid inquiry response: ' . json_last_error_msg());
        }

        $this->validateResponse($data);

        return $data;
    }

    /**
     * {@inheritDoc}
     * @throws EsewaException
     */
    public function payment(array $paymentData): array
    {
        $this->ensureAuthenticated();
        $this->validateTokenPayTransactionData($paymentData);

        $url = $this->getBaseUrl('token') . self::ENDPOINTS['payment'];
        $headers = $this->getAuthHeaders();

        $response = $this->httpClient->post($url, $paymentData, $headers);
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new EsewaException('Invalid payment response: ' . json_last_error_msg());
        }

        $this->validateResponse($data);

        return $data;
    }

    /**
     * {@inheritDoc}
     * @throws EsewaException
     */
    public function statusCheck(array $statusData): array
    {
        $this->ensureAuthenticated();
        $this->validateTokenPayTransactionData($statusData);

        $url = $this->getBaseUrl('token') . self::ENDPOINTS['status'];
        $headers = $this->getAuthHeaders();

        $response = $this->httpClient->post($url, $statusData, $headers);
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new EsewaException('Invalid status check response: ' . json_last_error_msg());
        }

        $this->validateResponse($data);

        return $data;
    }

    /**
     * {@inheritDoc}
     */
    public function setAccessToken(string $accessToken): void
    {
        $this->accessToken = $accessToken;
    }

    /**
     * {@inheritDoc}
     */
    public function verifySignature(array $data, string $signature): bool
    {
        // TokenPay-based API doesn't use signatures in the same way as ePay
        // This method is here for interface compliance
        return true;
    }

    /**
     * Ensures the client is authenticated.
     *
     * @throws EsewaException If not authenticated.
     */
    private function ensureAuthenticated(): void
    {
        if ($this->accessToken === null) {
            throw new EsewaException('Not authenticated. Please call authenticate() first.');
        }
    }

    /**
     * Gets authentication headers for API requests.
     *
     * @return array<string, string> The headers.
     */
    private function getAuthHeaders(): array
    {
        return [
            'Authorization' => $this->tokenType . ' ' . $this->accessToken,
            'Content-Type' => 'application/json',
        ];
    }

}

<?php

declare(strict_types=1);

namespace RemoteMerge\Esewa\Token;

use RemoteMerge\Esewa\PaymentInterface;

interface TokenInterface extends PaymentInterface
{
    /**
     * Authenticates with eSewa API and gets access token.
     *
     * @param string $username The API username.
     * @param string $password The API password.
     * @return array<string, mixed> Authentication response with access token.
     */
    public function authenticate(string $username, string $password): array;

    /**
     * Refreshes the access token.
     *
     * @param string $refreshToken The refresh token.
     * @return array<string, mixed> New authentication response.
     */
    public function refreshToken(string $refreshToken): array;

    /**
     * Inquires about a payment token.
     *
     * @param string $requestId The payment token/request ID.
     * @param array<string, mixed> $additionalParams Additional parameters.
     * @return array<string, mixed> Inquiry response.
     */
    public function inquiry(string $requestId, array $additionalParams = []): array;

    /**
     * Processes a payment.
     *
     * @param array<string, mixed> $paymentData Payment details.
     * @return array<string, mixed> Payment response.
     */
    public function payment(array $paymentData): array;

    /**
     * Checks payment status.
     *
     * @param array<string, mixed> $statusData Status check details.
     * @return array<string, mixed> Status response.
     */
    public function statusCheck(array $statusData): array;

    /**
     * Sets the access token for API requests.
     *
     * @param string $accessToken The access token.
     */
    public function setAccessToken(string $accessToken): void;
}

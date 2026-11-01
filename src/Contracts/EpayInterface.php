<?php

declare(strict_types=1);

namespace RemoteMerge\Esewa\Contracts;

interface EpayInterface extends PaymentInterface
{
    /**
     * Creates payment form data for eSewa ePay.
     *
     * The optional "properties" entry maps up to three custom field names (non-empty strings without square
     * brackets) to string, int or float values. Each one is returned as a "properties[name]" form field holding
     * the value cast to string. The signature does not cover properties.
     *
     * @param array<string, mixed> $paymentData Payment details.
     * @return array<string, mixed> Form data to be submitted.
     */
    public function createPayment(array $paymentData): array;

    /**
     * Verifies the payment response from eSewa.
     *
     * Confirms that the configured secret key signed the payload, that the signature covers transaction_code,
     * status, total_amount, transaction_uuid and product_code, and that product_code matches the configured
     * product code. It does not check the status value or make any network request. The caller must still match
     * the order, check the amount and confirm the transaction with checkStatus().
     *
     * @param string $encodedResponse The base64 encoded response.
     * @return array<string, mixed> The verified payment details.
     */
    public function verifyPayment(string $encodedResponse): array;

    /**
     * Checks the transaction status.
     *
     * @param string $transactionUuid The transaction UUID.
     * @param float $totalAmount The total amount.
     * @return array<string, mixed> The transaction status.
     */
    public function checkStatus(string $transactionUuid, float $totalAmount): array;

    /**
     * Gets the payment form action URL.
     *
     * @return string The form action URL.
     */
    public function getFormActionUrl(): string;
}

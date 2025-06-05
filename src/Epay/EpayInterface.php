<?php

declare(strict_types=1);

namespace RemoteMerge\Esewa\Epay;

use RemoteMerge\Esewa\PaymentInterface;

interface EpayInterface extends PaymentInterface
{
    /**
     * Creates payment form data for eSewa ePay.
     *
     * @param array<string, mixed> $paymentData Payment details.
     * @return array<string, mixed> Form data to be submitted.
     */
    public function createPayment(array $paymentData): array;

    /**
     * Verifies the payment response from eSewa.
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
